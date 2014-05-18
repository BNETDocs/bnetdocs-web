<?php

  if (php_sapi_name() != "cli") {
    http_response_code(503);
    header("Cache-Control: max-age=0, must-revalidate, no-cache, no-store");
    header("Content-Type: text/plain;charset=utf-8");
    echo "This file is intended to be executed via php's command line.";
    exit(1);
  }
  
  if ($argc > 1) {
    echo "Usage: php -f " . $argv[0] . "\n";
    exit(1);
  }
  
  final class Logger {

    const FILENAME    = "/home/nginx/bnetdocs/check-servers.log";
    const DATE_FORMAT = "Y-m-d H:i:s O";

    private static $fileHandle;

    public static function initialize() {
      self::$fileHandle = fopen(self::FILENAME, "w");
    }

    public static function finalize() {
      fclose(self::$fileHandle);
    }

    public static function write($message, $suppressTimestamp = true) {
      if (!$suppressTimestamp) {
        $timestamp = "\e[1;29m[" . date(self::DATE_FORMAT) . "] \e[0;0m";
        fwrite(self::$fileHandle, $timestamp);
        echo $timestamp;
      }
      fwrite(self::$fileHandle, $message);
      echo $message;
    }

    public static function writeLine($message) {
      self::write($message . "\n", false);
    }

  }

  final class DB {

    private $sql;

    public function __construct() {
      $this->sql = mysqli_init();
    }

    public function connect($host, $user, $pass, $db) {
      return @$this->sql->real_connect($host, $user, $pass, $db);
    }

    public function close() {
      return $this->sql->close();
    }

    public function error() {
      return $this->sql->error;
    }

    public function errno() {
      return $this->sql->errno;
    }

    public function escape($data) {
      return $this->sql->real_escape_string($data);
    }

    public function query($str) {
      $res = $this->sql->query($str);
      if (!is_object($res)) return false;
      $items = array();
      while ($item = $res->fetch_assoc()) {
        $items[] = $item;
      }
      $res->free();
      return $items;
    }

  }

  final class ServerCheck {

    const CONNECT_TIMEOUT = 5;

    private $sock;

    public function __construct() {
      $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    }

    public function __destruct() {
      if (isset($this->sock) && ($this->sock instanceof resource))
        socket_close($this->sock);
    }

    public function connect($address, $port) {
      $timeout = self::CONNECT_TIMEOUT;
      $time = time();
      socket_set_nonblock($this->sock);
      while (!@socket_connect($this->sock, $address, $port)) {
        $err = socket_last_error($this->sock);
        if ($err == 115 || $err == 114) {
          if ((time() - $time) >= $timeout) {
            socket_close($this->sock);
            return false;
          }
          sleep(0.1);
          continue;
        }
        return false;
      }
      socket_set_block($this->sock);
      return true;
    }

    public function close() {
      if (isset($this->sock) && ($this->sock instanceof resource))
        socket_close($this->sock);
      return true;
    }

    public function errno() {
      return socket_last_error($this->sock);
    }

    public function error() {
      return socket_strerror($this->errno());
    }

  }

  Logger::initialize();
  Logger::writeLine("\e[1;33mScript execution started.\e[0;0m");
  $script_start = microtime(true);
  $exit_code = 0;
  
  /**
   * Statistics definition
   **/
  $stats = array(
    "phoenix_servers"      => array(),
    "phoenix_server_types" => array(),
    "redux_servers"        => array(
      "bnet"               => array(),
      "bnls"               => array(),
    ),
    "timestamp"            => array(
      "utc_offset"         => (int)date("Z"),
      "start"              => $script_start,
      "end"                => null,
      "difference"         => null,
      "total_time"         => null,
    ),
  );

  /**
   * BNETDocs Redux's servers
   **/
  Logger::writeLine("\e[1;33mUpdating BNETDocs Redux server tables...\e[0;0m");
  $db = new DB();
  if (!$db->connect("127.0.0.1", "bnetdocs", "redux123", "bnetdocs_botdev")) {
    Logger::writeLine("\e[1;31mUnable to connect to the database.\e[0;0m");
    Logger::writeLine("\e[1;31mError #" . $db->errno() . ": " . $db->error() . "\e[0;0m");
    $exit_code = 1;
  } else {
    Logger::writeLine("\e[1;32mConnected to database server.\e[0;0m");

    /**
     * BNETDocs Redux's BNET servers
     **/
    $servers = $db->query("SELECT `id`, `serveraddress`, `port`, `version` FROM `bnetservers` ORDER BY `id` ASC;");
    if (!$servers) {
      Logger::writeLine("\e[1;31mFailed to retrieve the BNETDocs Redux BNET servers.\e[0;0m");
      Logger::writeLine("\e[1;31mError #" . $db->errno() . ": " . $db->error() . "\e[0;0m");
      $exit_code = 1;
    } else {
      foreach ($servers as $server) {
        $server_stats = array(
          "id"      => (int)$server["id"],
          "address" => (string)$server["serveraddress"],
          "port"    => (int)$server["port"],
          "version" => (string)$server["version"],
          "online"  => null,
        );
        $sock = new ServerCheck();
        if (!$sock->connect($server["serveraddress"], $server["port"])) {
          Logger::writeLine("\e[1;34mBNET\e[0;0m server is \e[1;31moffline\e[0;0m:  " . $server["serveraddress"] . ":" . $server["port"] . "\e[0;0m");
          $db->query("UPDATE `bnetservers` SET `status` = 'offline' WHERE `id` = '" . $db->escape($server["id"]) . "' LIMIT 1;");
          $server_stats["online"] = false;
        } else {
          Logger::writeLine("\e[1;34mBNET\e[0;0m server is \e[1;32monline\e[0;0m:   " . $server["serveraddress"] . ":" . $server["port"] . "\e[0;0m");
          $db->query("UPDATE `bnetservers` SET `status` = 'online' WHERE `id` = '" . $db->escape($server["id"]) . "' LIMIT 1;");
          $server_stats["online"] = true;
        }
        $stats["redux_servers"]["bnet"][] = $server_stats;
        $sock->close();
      }
    }
    
    /**
     * BNETDocs Redux's BNLS servers
     **/
    $servers = $db->query("SELECT `id`, `serveraddress`, `port` FROM `servers` ORDER BY `id` ASC;");
    if (!$servers) {
      Logger::writeLine("\e[1;31mFailed to retrieve the BNETDocs Redux BNLS servers.\e[0;0m");
      Logger::writeLine("\e[1;31mError #" . $db->errno() . ": " . $db->error() . "\e[0;0m");
      $exit_code = 1;
    } else {
      foreach ($servers as $server) {
        $server_stats = array(
          "id"      => (int)$server["id"],
          "address" => (string)$server["serveraddress"],
          "port"    => (int)$server["port"],
          "online"  => null,
        );
        $sock = new ServerCheck();
        if (!$sock->connect($server["serveraddress"], $server["port"])) {
          Logger::writeLine("\e[1;35mBNLS\e[0;0m server is \e[1;31moffline\e[0;0m:  " . $server["serveraddress"] . ":" . $server["port"] . "\e[0;0m");
          $db->query("UPDATE `servers` SET `status` = 'offline' WHERE `id` = '" . $db->escape($server["id"]) . "' LIMIT 1;");
          $server_stats["online"] = false;
        } else {
          Logger::writeLine("\e[1;35mBNLS\e[0;0m server is \e[1;32monline\e[0;0m:   " . $server["serveraddress"] . ":" . $server["port"] . "\e[0;0m");
          $db->query("UPDATE `servers` SET `status` = 'online' WHERE `id` = '" . $db->escape($server["id"]) . "' LIMIT 1;");
          $server_stats["online"] = true;
        }
        $stats["redux_servers"]["bnls"][] = $server_stats;
        $sock->close();
      }
    }
    
    $db->close();
  }
  
  /**
   * BNETDocs Phoenix's servers
   **/
  Logger::writeLine("\e[1;33mUpdating BNETDocs Phoenix server table...\e[0;0m");
  $db = new DB();
  if (!$db->connect("127.0.0.1", "bnetdocs", "redux123", "bnetdocs_phoenix")) {
    Logger::writeLine("\e[1;31mUnable to connect to the database.\e[0;0m");
    Logger::writeLine("\e[1;31mError #" . $db->errno() . ": " . $db->error() . "\e[0;0m");
    $exit_code = 1;
  } else {
    Logger::writeLine("\e[1;32mConnected to database server.\e[0;0m");

    define('STATUS_ONLINE',   1);
    define('STATUS_DISABLED', 2);

    $servers = $db->query("SELECT t.`id` AS `id`, t.`label` AS `label` FROM `server_types` t JOIN `servers` s ON t.`id` = s.`type` AND NOT s.`status` & " . STATUS_DISABLED . " GROUP BY t.`id` ASC;");
    if (!$servers) {
      Logger::writeLine("\e[1;31mFailed to retrieve the BNETDocs Phoenix server types.\e[0;0m");
      Logger::writeLine("\e[1;31mError #" . $db->errno() . ": " . $db->error() . "\e[0;0m");
      $exit_code = 1;
    } else {
      $types = array();
      foreach ($servers as $server) {
        $type_id    = (int)$server["id"];
        $type_label = (string)$server["label"];
        $types[]    = array($type_id, $type_label);
      }
      $stats["phoenix_server_types"] = $types;
    }

    $servers = $db->query("SELECT `id`, `address`, `port`, `type`, `label` FROM `servers` WHERE NOT (`status` & " . STATUS_DISABLED . ") ORDER BY `id` ASC;");
    if (!$servers) {
      Logger::writeLine("\e[1;31mFailed to retrieve the BNETDocs Phoenix servers.\e[0;0m");
      Logger::writeLine("\e[1;31mError #" . $db->errno() . ": " . $db->error() . "\e[0;0m");
      $exit_code = 1;
    } else {
      foreach ($servers as $server) {
        $server_stats = array(
          "id"         => (int)$server["id"],
          "address"    => (string)$server["address"],
          "port"       => (int)$server["port"],
          "type"       => (int)$server["type"],
          "label"      => (string)$server["label"],
          "online"     => null,
        );
        $sock = new ServerCheck();
        if (!$sock->connect($server["address"], $server["port"])) {
          Logger::writeLine("Server \e[1;36m" . $server["id"] . "\e[0;0m is \e[1;31moffline\e[0;0m:  " . $server["address"] . ":" . $server["port"] . "\e[0;0m");
          $db->query("UPDATE `servers` SET `status` = (`status` & ~" . STATUS_ONLINE . "), `date_updated` = NOW() WHERE `id` = '" . $db->escape($server["id"]) . "' LIMIT 1;");
          $server_stats["online"] = false;
        } else {
          Logger::writeLine("Server \e[1;36m" . $server["id"] . "\e[0;0m is \e[1;32monline\e[0;0m:   " . $server["address"] . ":" . $server["port"] . "\e[0;0m");
          $db->query("UPDATE `servers` SET `status` = (`status` | " . STATUS_ONLINE . "), `date_updated` = NOW() WHERE `id` = '" . $db->escape($server["id"]) . "' LIMIT 1;");
          $server_stats["online"] = true;
        }
        $stats["phoenix_servers"][] = $server_stats;
        $sock->close();
      }
    }
    
    $db->close();
  }
  
  /**
   * Logging mechanisms
   **/
  Logger::writeLine("\e[1;33mAdding log entry to records...\e[0;0m");
  $script_end = microtime(true);
  $script_time = $script_end - $script_start;
  $stats["timestamp"]["end"] = $script_end;
  $stats["timestamp"]["difference"] = $script_time;
  $stats["timestamp"]["total_time"] = sprintf("%01.2fs", $script_time);
  $str_stats = json_encode($stats);
  $db = new DB();
  if (!$db->connect("127.0.0.1", "bnetdocs", "redux123", "bnetdocs_phoenix")) {
    Logger::writeLine("\e[1;31mUnable to connect to the database.\e[0;0m");
    Logger::writeLine("\e[1;31mError #" . $db->errno() . ": " . $db->error() . "\e[0;0m");
    $exit_code = 1;
  } else {
    Logger::writeLine("\e[1;32mConnected to database server.\e[0;0m");

    $db->query("INSERT INTO `logs` (`type_id`, `event_date`, `content`) "
      . "SELECT `type_id`, "
      . "NOW() AS `event_date`, "
      . "'" . $db->escape($str_stats) . "' AS `content` "
      . "FROM `log_types` WHERE `type_name` = 'servers_refreshed_system';");
    if ($db->errno() == 0) {
      Logger::writeLine("\e[1;32mLog entry recorded to logs table.\e[0;0m");
    } else {
      Logger::writeLine("\e[1;31mLog entry failed to be recorded to logs table.\e[0;0m");
    }
    
    $db->close();
  }
  try {
    $fh = fopen("/home/nginx/bnetdocs/check-servers.json", "w");
    fwrite($fh, $str_stats);
    fclose($fh);
    Logger::writeLine("\e[1;32mLog entry recorded to filesystem.\e[0;0m");
  } catch (Exception $e) {
    Logger::writeLine("\e[1;31mLog entry failed to be recorded to filesystem.\e[0;0m");
  }

  $script_time = microtime(true) - $script_start;
  Logger::writeLine("\e[1;33mScript execution finished in \e[4;33m" . round($script_time, 2) . "s\e[0;33m\e[1;33m.\e[0;0m");
  Logger::finalize();
  exit($exit_code);
  
