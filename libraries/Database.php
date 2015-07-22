<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Common;
use \PDO;

class Database extends PDO {

  protected $hostname;
  protected $port;

  public function __construct($hostname, $port) {
    $this->hostname = $hostname;
    $this->port     = $port;
    $dsn = "mysql:"
      . "host=" . $this->hostname . ";"
      . "port=" . $this->port . ";"
      . "dbname=" . Common::$config->mysql->database . ";"
      . "charset=" . Common::$config->mysql->character_set;
    $username = Common::$config->mysql->username;
    $password = Common::$config->mysql->password;
    parent::__construct($dsn, $username, $password, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_TIMEOUT => Common::$config->mysql->timeout
    ]);
  }

  public function getHostname() {
    return $this->hostname;
  }

  public function getPort() {
    return $this->port;
  }
}
