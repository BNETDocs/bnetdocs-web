<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\ServerNotFoundException;
use \BNETDocs\Libraries\ServerType;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Database;
use \CarlBennett\MVC\Libraries\DatabaseDriver;

use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \JsonSerializable;
use \PDO;
use \PDOException;
use \StdClass;

class Server implements JsonSerializable {

  const STATUS_ONLINE   = 0x00000001;
  const STATUS_DISABLED = 0x00000002;

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
        ORDER BY
          `type_id` ASC,
          ISNULL(`label`) ASC,
          `label` ASC,
          `address` ASC,
          `id` ASC;
      ");
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh servers");
      }
      $objects = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $objects[] = new self($row);
      }
      $stmt->closeCursor();
      return $objects;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh servers", $e);
    }
    return null;
  }

  public function getCreatedDateTime() {
    if (is_null($this->created_datetime)) {
      return $this->created_datetime;
    } else {
      $tz = new DateTimeZone( 'Etc/UTC' );
      $dt = new DateTime($this->created_datetime);
      $dt->setTimezone($tz);
      return $dt;
    }
  }

  public function getName() {
    return (empty($this->label) ?
      $this->address . ":" . $this->port :
      $this->label
    );
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

  public static function getServersByUserId($user_id) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare('
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
        WHERE `user_id` = :user_id
        ORDER BY `id` ASC;
      ');
      $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException('Cannot query servers by user id');
      }
      $objects = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $objects[] = new self($row);
      }
      $stmt->closeCursor();
      return $objects;
    } catch (PDOException $e) {
      throw new QueryException('Cannot query servers by user id', $e);
    }
    return null;
  }

  public function getStatusBitmask() {
    return $this->status_bitmask;
  }

  public function getType() {
    return new ServerType($this->type_id);
  }

  public function getTypeId() {
    return $this->type_id;
  }

  public function getURI() {
    $value = $this->getLabel();
    if (empty($value)) {
      $value = $this->getAddress() . ":" . $this->getPort();
    }
    return Common::relativeUrlToAbsolute(
      "/server/" . $this->getId() . "/" . Common::sanitizeForUrl($value, true)
    );
  }

  public function getUpdatedDateTime() {
    if (is_null($this->updated_datetime)) {
      return $this->updated_datetime;
    } else {
      $tz = new DateTimeZone( 'Etc/UTC' );
      $dt = new DateTime($this->updated_datetime);
      $dt->setTimezone($tz);
      return $dt;
    }
  }

  public function getUser() {
    return User::findUserById($this->user_id);
  }

  public function getUserId() {
    return $this->user_id;
  }

  public function isDisabled() {
    return ($this->status_bitmask & self::STATUS_DISABLED);
  }

  public function isOffline() {
    return (!$this->isOnline());
  }

  public function isOnline() {
    return ($this->status_bitmask & self::STATUS_ONLINE);
  }

  public function jsonSerialize() {
    $created_datetime = $this->getCreatedDateTime();
    if (!is_null($created_datetime)) $created_datetime = [
      "iso"  => $created_datetime->format("r"),
      "unix" => $created_datetime->getTimestamp(),
    ];

    $updated_datetime = $this->getUpdatedDateTime();
    if (!is_null($updated_datetime)) $updated_datetime = [
      "iso"  => $updated_datetime->format("r"),
      "unix" => $updated_datetime->getTimestamp(),
    ];

    return [
      "address"          => $this->getAddress(),
      "created_datetime" => $created_datetime,
      "id"               => $this->getId(),
      "label"            => $this->getLabel(),
      "port"             => $this->getPort(),
      "status_bitmask"   => $this->getStatusBitmask(),
      "type_id"          => $this->getTypeId(),
      "updated_datetime" => $updated_datetime,
      "uri"              => $this->getURI(),
      "user"             => $this->getUser(),
    ];
  }

  protected static function normalize(StdClass &$data) {
    $data->address          = (string) $data->address;
    $data->created_datetime = (string) $data->created_datetime;
    $data->id               = (int)    $data->id;
    $data->port             = (int)    $data->port;
    $data->status_bitmask   = (int)    $data->status_bitmask;
    $data->type_id          = (int)    $data->type_id;

    if (!is_null($data->label))
      $data->label = (string) $data->label;

    if (!is_null($data->updated_datetime))
      $data->updated_datetime = (string) $data->updated_datetime;

    if (!is_null($data->user_id))
      $data->user_id = (int) $data->user_id;

    return true;
  }

  public function refresh() {
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
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh server", $e);
    }
    return false;
  }

  public function save() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {

      $stmt = Common::$database->prepare('
        UPDATE
          `servers`
        SET
          `address` = :address,
          `created_datetime` = :created_dt,
          `label` = :label,
          `port` = :port,
          `status_bitmask` = :status,
          `type_id` = :type_id,
          `updated_datetime` = :updated_dt,
          `user_id` = :user_id
        WHERE
          `id` = :id
        LIMIT 1;
      ');

      $stmt->bindParam( ':address', $this->address, PDO::PARAM_STR );
      $stmt->bindParam(
        ':created_dt', $this->created_datetime, PDO::PARAM_STR
      );
      $stmt->bindParam( ':label', $this->label, PDO::PARAM_STR );
      $stmt->bindParam( ':id', $this->id, PDO::PARAM_INT );
      $stmt->bindParam( ':port', $this->port, PDO::PARAM_INT );
      $stmt->bindParam( ':status', $this->status_bitmask, PDO::PARAM_INT );
      $stmt->bindParam( ':type_id', $this->type_id, PDO::PARAM_INT );

      $stmt->bindParam( ':updated_dt', $this->updated_datetime, (
        is_null( $this->updated_datetime ) ? PDO::PARAM_NULL : PDO::PARAM_STR
      ));

      $stmt->bindParam( ':user_id', $this->user_id, (
        is_null( $this->user_id ) ? PDO::PARAM_NULL : PDO::PARAM_INT
      ));

      if (!$stmt->execute()) {
        throw new QueryException( 'Cannot save server' );
      }
      $stmt->closeCursor();
      return true;

    } catch ( PDOException $e ) {
      throw new QueryException( 'Cannot save server', $e );
    }

    return false;
  }

  public function setStatusBitmask( $status_bitmask ) {
    $this->status_bitmask = $status_bitmask;
  }

}
