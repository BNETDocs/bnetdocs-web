<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Common;

class Database {

  protected $mysql;

  public function __construct() {
    $this->mysql = \mysqli_init();
  }

  function __destruct() {
    $this->close();
  }

  public function close() {
    return $this->mysql->close();
  }

  public function connect() {
    $this->mysql->options(
      \MYSQLI_OPT_CONNECT_TIMEOUT,
      Common::$config->mysql->connect_timeout
    );
    $result = $this->mysql->real_connect(
      Common::$config->mysql->hostname,
      Common::$config->mysql->username,
      Common::$config->mysql->password,
      Common::$config->mysql->name,
      Common::$config->mysql->port
    );
    if (!$result) return $result;
    $this->mysql->set_charset(
      Common::$config->mysql->character_set
    );
    return $result;
  }

  public function is_connected() {
    if (!$this->mysql instanceof \mysqli) return false;
    if (!is_resource($this->mysql)) return false;
    return $this->mysql->ping();
  }

}
