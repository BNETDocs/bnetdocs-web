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

  protected static $event_types = null;

  public static function &getAllEvents() {
    $event_log = [];
    // TODO
    return $event_log;
  }

  public static function &getEventType($event_name) {
    if (is_null(self::$event_types)) {
      self::getEventTypes();
    }
    if (!is_string($event_name)) {
      throw new InvalidArgumentException("Event type must be string");
    } else if (!isset(self::$event_types[$event_name])) {
      throw new RuntimeException("Event type not found");
    }
    return (self::$event_types[$event_name]);
  }

  protected static function &getEventTypes() {
    $cache_key = "bnetdocs-logger-eventtypes";
    $cache_val = Common::$cache->get($cache_key);
    if ($cache_val !== false) {
      self::$event_types = unserialize($cache_val);
      return self::$event_types;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $stmt = Common::$database->prepare("
      SELECT `id`, `name`, `label` FROM `event_types`;
    ");
    $stmt->execute();
    $event_types = [];
    while ($obj = $stmt->fetch(PDO::FETCH_OBJ)) {
      $event_types[$obj->name] = $obj;
    }
    $stmt->closeCursor();
    Common::$cache->set($cache_key, serialize($event_types), 3600);
    self::$event_types = $event_types;
    return self::$event_types;
  }

  public static function logEvent(
    $event_type, $user_id = null, $ip_address = null, $meta_data = null
  ) {
    if (is_null(self::$event_types)) {
      self::getEventTypes();
    }
    if (!is_string($event_type)) {
      throw new InvalidArgumentException("Event type must be string");
    } else if (!isset(self::$event_types[$event_type])) {
      throw new RuntimeException("Event type not found");
    }
    $event_type_id = (int)self::$event_types[$event_type]->id;
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $successful = false;
    try {
      $stmt = Common::$database->prepare("
        INSERT INTO `event_log` (
          `event_type_id`, `event_datetime`, `user_id`, `ip_address`,
          `meta_data`
        ) VALUES (
          :event_type_id, NOW(), :user_id, :ip_address, :meta_data
        );
      ");
      $stmt->bindParam(":event_type_id", $event_type_id, PDO::PARAM_INT);
      $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
      $stmt->bindParam(":ip_address", $ip_address, PDO::PARAM_STR);
      $stmt->bindParam(":meta_data", $meta_data, PDO::PARAM_STR);
      $successful = $stmt->execute();
      $stmt->closeCursor();
    } catch (PDOException $e) {
      throw new QueryException("Cannot log event", $e);
    } finally {
      return $successful;
    }
  }

}
