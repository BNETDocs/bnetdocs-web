<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Server;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Database;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \DateTime;
use \DateTimeZone;
use \PDO;
use \PDOException;

class ServerMetric {

  const CACHE_RESPONSE_TIME_TTL = 300;
  const CACHE_UPTIME_TTL        = 300;

  public static function getLatestResponseTime($server_id) {
    $cache_key = "bnetdocs-servermetric-responsetime-" . (int) $server_id;
    $cache_val = Common::$cache->get($cache_key);
    if ($cache_val !== false) return $cache_val;
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $stmt = Common::$database->prepare("
      SELECT `response_time`
      FROM `server_metrics`
      WHERE `server_id` = :server_id
      ORDER BY `metric_datetime` DESC LIMIT 1;
    ");
    $stmt->bindParam(":server_id", $server_id, PDO::PARAM_INT);
    $stmt->execute();
    $obj = $stmt->fetch(PDO::FETCH_OBJ);
    $stmt->closeCursor();
    Common::$cache->set(
      $cache_key,
      $obj->response_time,
      self::CACHE_RESPONSE_TIME_TTL
    );
    return $obj->response_time;
  }

  public static function getUptime($server_id) {
    $cache_key = "bnetdocs-servermetric-uptime-" . (int) $server_id;
    $cache_val = Common::$cache->get($cache_key);
    if ($cache_val !== false) return unserialize($cache_val);
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $stmt = Common::$database->prepare("
      SELECT
        MIN(`metric_datetime`) AS `lbound`,
        MAX(`metric_datetime`) AS `ubound`
      FROM `server_metrics`
      WHERE `server_id` = :server_id AND `metric_datetime` >= (
        SELECT `metric_datetime`
        FROM `server_metrics`
        WHERE `server_id` = :server_id AND NOT (`metric_flags` & "
          . Server::STATUS_ONLINE . ")
        ORDER BY `metric_datetime` DESC LIMIT 1
      );
    ");
    $stmt->bindParam(":server_id", $server_id, PDO::PARAM_INT);
    $stmt->execute();
    $obj = $stmt->fetch(PDO::FETCH_OBJ);
    $stmt->closeCursor();

    $tz     = new DateTimeZone("UTC");
    $lbound = new DateTime($obj->lbound, $tz);
    $ubound = new DateTime($obj->ubound, $tz);
    $range  = $ubound->diff($lbound);

    Common::$cache->set($cache_key, serialize($range), self::CACHE_UPTIME_TTL);
    return $range;
  }

}
