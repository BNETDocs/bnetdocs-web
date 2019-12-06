<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\User;
use \BNETDocs\Libraries\VersionInfo;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Logger as LoggerMVCLib;
use \Exception;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \RuntimeException;

class Logger extends LoggerMVCLib {

  public static function logEvent(
    $event_type_id, $user_id = null, $ip_address = null, $meta_data = null
  ) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $successful = false;

    try {

      $stmt = Common::$database->prepare('
        INSERT INTO `event_log` (
          `event_type_id`, `event_datetime`, `user_id`, `ip_address`,
          `meta_data`
        ) VALUES (
          :event_type_id, NOW(), :user_id, :ip_address, :meta_data
        );
      ');

      $stmt->bindParam(':event_type_id', $event_type_id, PDO::PARAM_INT);

      $t = (is_null($user_id) ? PDO::PARAM_NULL : PDO::PARAM_INT);
      $stmt->bindParam(':user_id', $user_id, $t);

      $t = (is_null($ip_address) ? PDO::PARAM_NULL : PDO::PARAM_STR);
      $stmt->bindParam(':ip_address', $ip_address, $t);

      $t = (is_null($meta_data) ? PDO::PARAM_NULL : PDO::PARAM_STR);
      $stmt->bindParam(':meta_data', $meta_data, $t);

      $successful = $stmt->execute();
      $stmt->closeCursor();

    } catch (PDOException $e) {
      throw new QueryException('Cannot log event', $e);

    } finally {
      return $successful;
    }
  }

}
