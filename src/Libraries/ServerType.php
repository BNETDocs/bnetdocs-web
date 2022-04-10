<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\ServerTypeNotFoundException;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Database;
use \CarlBennett\MVC\Libraries\DatabaseDriver;

use \InvalidArgumentException;
use \JsonSerializable;
use \PDO;
use \PDOException;
use \StdClass;

class ServerType implements JsonSerializable {

  protected $id;
  protected $label;

  public function __construct($data) {
    if (is_numeric($data)) {
      $this->id    = (int) $data;
      $this->label = null;
      $this->refresh();
    } else if ($data instanceof StdClass) {
      self::normalize($data);
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
      }
      $objects = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $objects[] = new self($row);
      }
      $stmt->closeCursor();
      return $objects;
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

  public function jsonSerialize() {
    return array(
      'id'    => $this->id,
      'label' => $this->label,
    );
  }

  protected static function normalize(StdClass &$data) {
    $data->id    = (int)    $data->id;
    $data->label = (string) $data->label;

    return true;
  }

  public function refresh() {
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
      self::normalize($row);
      $this->label = $row->label;
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh server type", $e);
    }
    return false;
  }

}
