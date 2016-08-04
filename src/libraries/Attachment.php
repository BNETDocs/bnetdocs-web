<?php

namespace BNETDocs\Libraries;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Database;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\AttachmentNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\User;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \JsonSerializable;
use \PDO;
use \PDOException;
use \StdClass;

class Attachment implements JsonSerializable {

  const PARENT_TYPE_DOCUMENT  = 0;
  const PARENT_TYPE_COMMENT   = 1;
  const PARENT_TYPE_NEWS_POST = 2;
  const PARENT_TYPE_PACKET    = 3;
  const PARENT_TYPE_SERVER    = 4;
  const PARENT_TYPE_USER      = 5;

  protected $created_datetime;
  protected $filename;
  protected $id;
  protected $parent_id;
  protected $parent_type;
  protected $user_id;

  public function __construct($data) {
    if (is_numeric($data)) {
      $this->created_datetime = null;
      $this->filename         = null;
      $this->id               = (int) $data;
      $this->parent_id        = null;
      $this->parent_type      = null;
      $this->user_id          = null;
      $this->refresh();
    } else if ($data instanceof StdClass) {
      self::normalize($data);
      $this->created_datetime = $data->created_datetime;
      $this->filename         = $data->filename;
      $this->id               = $data->id;
      $this->parent_id        = $data->parent_id;
      $this->parent_type      = $data->parent_type;
      $this->user_id          = $data->user_id;
    } else {
      throw new InvalidArgumentException("Cannot use data argument");
    }
  }

  public static function getAll($parent_type, $parent_id) {
    $ck = "bnetdocs-attachment-" . $parent_type . "-" . $parent_id;
    $cv = Common::$cache->get($ck);
    if ($cv !== false && !empty($cv)) {
      $ids     = explode(",", $cv);
      $objects = [];
      foreach ($ids as $id) {
        $objects[] = new self($id);
      }
      return $objects;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `created_datetime`,
          `filename`,
          `id`,
          `parent_id`,
          `parent_type`,
          `user_id`
        FROM `attachments`
        WHERE
          `parent_type` = :parent_type AND
          `parent_id` = :parent_id
        ORDER BY
          `filename` ASC,
          `created_datetime` ASC,
          `id` ASC
        ;
      ");
      $stmt->bindParam(":parent_type", $parent_type, PDO::PARAM_INT);
      $stmt->bindParam(":parent_id", $parent_id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh attachment");
      }
      $ids     = [];
      $objects = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $ids[]     = (int) $row->id;
        $objects[] = new self($row);
        Common::$cache->set(
          "bnetdocs-attachment-" . $row->id, serialize($row), 300
        );
      }
      $stmt->closeCursor();
      Common::$cache->set($ck, implode(",", $ids), 300);
      return $objects;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh attachment", $e);
    }
    return null;
  }

  public function getContent() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT `content`
        FROM `attachments`
        WHERE `id` = :id
        LIMIT 1;
      ");
      $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot get attachment content");
      } else if ($stmt->rowCount() == 0) {
        throw new AttachmentNotFoundException($this->id);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      return $row->content;
    } catch (PDOException $e) {
      throw new QueryException("Cannot get attachment content", $e);
    }
    return false;
  }

  public function getContentSize($format = false) {
    $bytes = strlen($this->getContent());

    if ($format) {
      $bytes = Common::formatFileSize($bytes);
    }

    return $bytes;
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

  public function getFilename() {
    return $this->filename;
  }

  public function getId() {
    return $this->id;
  }

  public function getParentId() {
    return $this->parent_id;
  }

  public function getParentType() {
    return $this->parent_type;
  }

  public function getUser() {
    if (is_null($this->user_id)) return null;
    return new User($this->user_id);
  }

  public function getUserId() {
    return $this->user_id;
  }

  public function jsonSerialize() {
    $created_datetime = $this->getCreatedDateTime();
    if (!is_null($created_datetime)) $created_datetime = [
      "iso"  => $created_datetime->format("r"),
      "unix" => $created_datetime->getTimestamp(),
    ];

    return [
      "created_datetime" => $created_datetime,
      "filename"         => $this->getFilename(),
      "id"               => $this->getId(),
      "parent_id"        => $this->getParentId(),
      "parent_type"      => $this->getParentType(),
      "user"             => $this->getUser(),
    ];
  }

  protected static function normalize(StdClass &$data) {
    $data->created_datetime = (string) $data->created_datetime;
    $data->filename         = (string) $data->filename;
    $data->id               = (int)    $data->id;
    $data->parent_id        = (int)    $data->parent_id;
    $data->parent_type      = (int)    $data->parent_type;
    $data->user_id          = (int)    $data->user_id;

    return true;
  }

  public function refresh() {
    $ck = "bnetdocs-attachment-" . $this->id;
    $cv = Common::$cache->get($ck);
    if ($cv !== false) {
      $cv = unserialize($cv);
      $this->created_datetime = $cv->created_datetime;
      $this->filename         = $cv->filename;
      $this->id               = $cv->id;
      $this->parent_id        = $cv->parent_id;
      $this->parent_type      = $cv->parent_type;
      $this->user_id          = $cv->user_id;
      return true;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `created_datetime`,
          `filename`,
          `id`,
          `parent_id`,
          `parent_type`,
          `user_id`
        FROM `attachments`
        WHERE `id` = :id
        LIMIT 1;
      ");
      $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh attachment");
      } else if ($stmt->rowCount() == 0) {
        throw new AttachmentNotFoundException($this->id);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      self::normalize($row);
      $this->created_datetime = $row->created_datetime;
      $this->filename         = $row->filename;
      $this->id               = $row->id;
      $this->parent_id        = $row->parent_id;
      $this->parent_type      = $row->parent_type;
      $this->user_id          = $row->user_id;
      Common::$cache->set($ck, serialize($row), 300);
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh attachment", $e);
    }
    return false;
  }

}
