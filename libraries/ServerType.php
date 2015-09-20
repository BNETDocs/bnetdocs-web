<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\ServerTypeNotFoundException;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;

class ServerType {

  protected $id;
  protected $label;

  public function __construct($data) {
    if (is_numeric($data)) {
      $this->id    = (int)$data;
      $this->label = null;
      $this->refresh();
    } else if ($data instanceof StdClass) {
      $this->id    = $data->id;
      $this->label = $data->label;
    } else {
      throw new InvalidArgumentException("Cannot use data argument");
    }
  }

  public static function getAllServerTypes() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `id`,
          `label`
        FROM `server_types`
        ORDER BY `id` ASC;
      ");
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh server types");
      } else if ($stmt->rowCount() == 0) {
        throw new ServerTypeNotFoundException(null);
      }
      $servers = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $servers[] = new self($row);
        Common::$cache->set(
          "bnetdocs-servertype-" . $row->id, serialize($row), 300
        );
      }
      $stmt->closeCursor();
      return $servers;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh server types", $e);
    }
    return null;
  }

  public function getId() {
    return $this->id;
  }

  public function getLabel() {
    return $this->label;
  }
  
  public function refresh() {
    $cache_key = "bnetdocs-servertype-" . $this->id;
    $cache_val = Common::$cache->get($cache_key);
    if ($cache_val !== false) {
      $cache_val = unserialize($cache_val);
      $this->label = $cache_val->label;
      return true;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `id`,
          `label`
        FROM `server_types`
        WHERE `id` = :id
        LIMIT 1;
      ");
      $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh server type");
      } else if ($stmt->rowCount() == 0) {
        throw new ServerTypeNotFoundException($this->id);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      $this->label = $row->label;
      Common::$cache->set($cache_key, serialize($row), 300);
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh server type", $e);
    }
    return false;
  }

}
