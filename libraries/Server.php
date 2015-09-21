<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\ServerNotFoundException;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;

class Server {

  const STATUS_ONLINE   = 1;
  const STATUS_DISABLED = 2;

  protected $address;
  protected $created_datetime;
  protected $id;
  protected $label;
  protected $port;
  protected $status_bitmask;
  protected $type_id;
  protected $updated_datetime;
  protected $user_id;
  
  public function __construct($data) {
    if (is_numeric($data)) {
      $this->address          = null;
      $this->created_datetime = null;
      $this->id               = (int) $data;
      $this->label            = null;
      $this->port             = null;
      $this->status_bitmask   = null;
      $this->type_id          = null;
      $this->updated_datetime = null;
      $this->user_id          = null;
      $this->refresh();
    } else if ($data instanceof StdClass) {
      self::normalize($data);
      $this->address          = $data->address;
      $this->created_datetime = $data->created_datetime;
      $this->id               = $data->id;
      $this->label            = $data->label;
      $this->port             = $data->port;
      $this->status_bitmask   = $data->status_bitmask;
      $this->type_id          = $data->type_id;
      $this->updated_datetime = $data->updated_datetime;
      $this->user_id          = $data->user_id;
    } else {
      throw new InvalidArgumentException("Cannot use data argument");
    }
  }

  public function getAddress() {
    return $this->address;
  }

  public static function getAllServers() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `address`,
          `created_datetime`,
          `id`,
          `label`,
          `port`,
          `status_bitmask`,
          `type_id`,
          `updated_datetime`,
          `user_id`
        FROM `servers`
        ORDER BY `type_id` ASC, `label` ASC, `address` ASC, `id` ASC;
      ");
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh servers");
      } else if ($stmt->rowCount() == 0) {
        throw new ServerNotFoundException(null);
      }
      $servers = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $servers[] = new self($row);
        Common::$cache->set(
          "bnetdocs-server-" . $row->id, serialize($row), 300
        );
      }
      $stmt->closeCursor();
      return $servers;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh servers", $e);
    }
    return null;
  }

  public function getCreatedDateTime() {
    if (is_null($this->created_datetime)) {
      return $this->created_datetime;
    } else {
      $tz = new DateTimeZone("UTC");
      $dt = new DateTime($this->created_datetime);
      $dt->setTimezone($tz);
      return $dt;
    }
  }

  public function getId() {
    return $this->id;
  }

  public function getLabel() {
    return $this->label;
  }

  public function getPort() {
    return $this->port;
  }

  public function getStatusBitmask() {
    return $this->status_bitmask;
  }

  public function getTypeId() {
    return $this->type_id;
  }

  public function getUpdatedDateTime() {
    if (is_null($this->updated_datetime)) {
      return $this->updated_datetime;
    } else {
      $tz = new DateTimeZone("UTC");
      $dt = new DateTime($this->updated_datetime);
      $dt->setTimezone($tz);
      return $dt;
    }
  }

  public function getUser() {
    return new User($this->user_id);
  }

  public function getUserId() {
    return $this->user_id;
  }

  protected static function normalize(StdClass &$data) {
    $data->address          = (string) $data->address;
    $data->created_datetime = (string) $data->created_datetime;
    $data->label            = (string) $data->label;
    $data->port             = (int)    $data->port;
    $data->status_bitmask   = (int)    $data->status_bitmask;
    $data->type_id          = (int)    $data->type_id;

    if (!is_null($data->updated_datetime))
      $data->updated_datetime = (string) $data->updated_datetime;

    if (!is_null($data->user_id))
      $data->user_id = (int) $data->user_id;

    return true;
  }
  
  public function refresh() {
    $cache_key = "bnetdocs-server-" . $this->id;
    $cache_val = Common::$cache->get($cache_key);
    if ($cache_val !== false) {
      $cache_val = unserialize($cache_val);
      $this->address          = $cache_val->address;
      $this->created_datetime = $cache_val->created_datetime;
      $this->label            = $cache_val->label;
      $this->port             = $cache_val->port;
      $this->status_bitmask   = $cache_val->status_bitmask;
      $this->type_id          = $cache_val->type_id;
      $this->updated_datetime = $cache_val->updated_datetime;
      $this->user_id          = $cache_val->user_id;
      return true;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `address`,
          `created_datetime`,
          `id`,
          `label`,
          `port`,
          `status_bitmask`,
          `type_id`,
          `updated_datetime`,
          `user_id`
        FROM `servers`
        WHERE `id` = :id
        LIMIT 1;
      ");
      $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh server");
      } else if ($stmt->rowCount() == 0) {
        throw new ServerNotFoundException($this->id);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      self::normalize($row);
      $this->address          = $row->address;
      $this->created_datetime = $row->created_datetime;
      $this->label            = $row->label;
      $this->port             = $row->port;
      $this->status_bitmask   = $row->status_bitmask;
      $this->type_id          = $row->type_id;
      $this->updated_datetime = $row->updated_datetime;
      $this->user_id          = $row->user_id;
      Common::$cache->set($cache_key, serialize($row), 300);
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh server", $e);
    }
    return false;
  }

}
