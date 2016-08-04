<?php

namespace BNETDocs\Libraries;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\Exceptions\DatabaseUnavailableException;
use \BNETDocs\Libraries\Logger;
use \PDOException;

class DatabaseDriver {

  public static function getDatabaseObject() {
    $last_exception = null;
    $servers        = self::getServers();
    foreach ($servers as $server) {
      try {
        $connection = new Database($server->hostname, $server->port);
        return $connection;
      } catch (PDOException $exception) {
        Logger::logMetric("dbhost", $server->hostname);
        Logger::logMetric("dbport", $server->port);
        Logger::logException($exception);
        $last_exception = $exception;
      }
    }
    throw new DatabaseUnavailableException($last_exception);
  }

  public static function getServers() {
    return Common::$config->mysql->servers;
  }

}
