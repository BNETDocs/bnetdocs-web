<?php

namespace BNETDocs\Libraries;

use \CarlBennett\MVC\Libraries\Database;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\CommentNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Markdown;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \JsonSerializable;
use \PDO;
use \PDOException;
use \StdClass;

class Comment implements JsonSerializable {

  const PARENT_TYPE_DOCUMENT  = 0;
  const PARENT_TYPE_COMMENT   = 1;
  const PARENT_TYPE_NEWS_POST = 2;
  const PARENT_TYPE_PACKET    = 3;
  const PARENT_TYPE_SERVER    = 4;
  const PARENT_TYPE_USER      = 5;

  protected $content;
  protected $created_datetime;
  protected $edited_count;
  protected $edited_datetime;
  protected $id;
  protected $parent_id;
  protected $parent_type;
  protected $user_id;

  public function __construct($data) {
    if (is_numeric($data)) {
      $this->content          = null;
      $this->created_datetime = null;
      $this->edited_count     = null;
      $this->edited_datetime  = null;
      $this->id               = (int) $data;
      $this->parent_id        = null;
      $this->parent_type      = null;
      $this->user_id          = null;
      $this->refresh();
    } else if ($data instanceof StdClass) {
      self::normalize($data);
      $this->content          = $data->content;
      $this->created_datetime = $data->created_datetime;
      $this->edited_count     = $data->edited_count;
      $this->edited_datetime  = $data->edited_datetime;
      $this->id               = $data->id;
      $this->parent_id        = $data->parent_id;
      $this->parent_type      = $data->parent_type;
      $this->user_id          = $data->user_id;
    } else {
      throw new InvalidArgumentException("Cannot use data argument");
    }
  }

  public static function create($parent_type, $parent_id, $user_id, $content) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $successful = false;
    try {
      $stmt = Common::$database->prepare("
        INSERT INTO `comments` (
          `id`, `parent_type`, `parent_id`, `user_id`, `created_datetime`,
          `edited_count`, `edited_datetime`, `content`
        ) VALUES (
          NULL, :parent_type, :parent_id, :user_id, NOW(), 0, NULL, :content
        );
      ");
      $stmt->bindParam(":parent_type", $parent_type, PDO::PARAM_INT);
      $stmt->bindParam(":parent_id", $parent_id, PDO::PARAM_INT);
      $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
      $stmt->bindParam(":content", $content, PDO::PARAM_STR);
      $successful = $stmt->execute();
      $stmt->closeCursor();
    } catch (PDOException $e) {
      throw new QueryException("Cannot create comment", $e);
    } finally {
      return $successful;
    }
  }

  public static function delete($id, $parent_type, $parent_id) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $successful = false;
    try {
      $stmt = Common::$database->prepare("
        DELETE FROM `comments` WHERE `id` = :id LIMIT 1;
      ");
      $stmt->bindParam(":id", $id, PDO::PARAM_INT);
      $successful = $stmt->execute();
      $stmt->closeCursor();
    } catch (PDOException $e) {
      throw new QueryException("Cannot delete comment", $e);
    } finally {
      return $successful;
    }
  }

  public static function getAll($parent_type, $parent_id) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `content`,
          `created_datetime`,
          `edited_count`,
          `edited_datetime`,
          `id`,
          `parent_id`,
          `parent_type`,
          `user_id`
        FROM `comments`
        WHERE
          `parent_type` = :parent_type AND
          `parent_id` = :parent_id
        ORDER BY
          `created_datetime` ASC,
          `id` ASC
        ;
      ");
      $stmt->bindParam(":parent_type", $parent_type, PDO::PARAM_INT);
      $stmt->bindParam(":parent_id", $parent_id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh comment");
      }
      $objects = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $objects[] = new self($row);
      }
      $stmt->closeCursor();
      return $objects;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh comment", $e);
    }
    return null;
  }

  public function getContent($prepare) {
    if (!$prepare) {
      return $this->content;
    }
    $md = new Markdown();
    return $md->text(filter_var($this->content, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
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

  public function getEditedCount() {
    return $this->edited_count;
  }

  public function getEditedDateTime() {
    if (is_null($this->edited_datetime)) {
      return $this->edited_datetime;
    } else {
      $tz = new DateTimeZone( 'Etc/UTC' );
      $dt = new DateTime($this->edited_datetime);
      $dt->setTimezone($tz);
      return $dt;
    }
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
    return User::findUserById($this->user_id);
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

    $edited_datetime = $this->getEditedDateTime();
    if (!is_null($edited_datetime)) $edited_datetime = [
      "iso"  => $edited_datetime->format("r"),
      "unix" => $edited_datetime->getTimestamp(),
    ];

    return [
      "content"          => $this->getContent(true),
      "created_datetime" => $created_datetime,
      "edited_count"     => $this->getEditedCount(),
      "edited_datetime"  => $edited_datetime,
      "id"               => $this->getId(),
      "parent_id"        => $this->getParentId(),
      "parent_type"      => $this->getParentType(),
      "user"             => $this->getUser(),
    ];
  }

  protected static function normalize(StdClass &$data) {
    $data->content          = (string) $data->content;
    $data->created_datetime = (string) $data->created_datetime;
    $data->edited_count     = (int)    $data->edited_count;
    $data->edited_datetime  = (string) $data->edited_datetime;
    $data->id               = (int)    $data->id;
    $data->parent_id        = (int)    $data->parent_id;
    $data->parent_type      = (int)    $data->parent_type;
    $data->user_id          = (int)    $data->user_id;

    return true;
  }

  public function refresh() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `content`,
          `created_datetime`,
          `edited_count`,
          `edited_datetime`,
          `id`,
          `parent_id`,
          `parent_type`,
          `user_id`
        FROM `comments`
        WHERE `id` = :id
        LIMIT 1;
      ");
      $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh comment");
      } else if ($stmt->rowCount() == 0) {
        throw new CommentNotFoundException($this->id);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      self::normalize($row);
      $this->content          = $row->content;
      $this->created_datetime = $row->created_datetime;
      $this->edited_count     = $row->edited_count;
      $this->edited_datetime  = $row->edited_datetime;
      $this->id               = $row->id;
      $this->parent_id        = $row->parent_id;
      $this->parent_type      = $row->parent_type;
      $this->user_id          = $row->user_id;
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh comment", $e);
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
          `comments`
        SET
          `content` = :content,
          `created_datetime` = :created_dt,
          `edited_count` = :edited_count,
          `edited_datetime` = :edited_dt,
          `parent_id` = :parent_id,
          `parent_type` = :parent_type,
          `user_id` = :user_id
        WHERE
          `id` = :id
        LIMIT 1;
      ');
      $stmt->bindParam(':content', $this->content, PDO::PARAM_STR);
      $stmt->bindParam(':created_dt', $this->created_datetime, PDO::PARAM_STR);
      $stmt->bindParam(':edited_count', $this->edited_count, PDO::PARAM_INT);
      $stmt->bindParam(':edited_dt', $this->edited_datetime, PDO::PARAM_STR);
      $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
      $stmt->bindParam(':parent_id', $this->parent_id, PDO::PARAM_INT);
      $stmt->bindParam(':parent_type', $this->parent_type, PDO::PARAM_INT);
      $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException( 'Cannot save comment' );
      }
      $stmt->closeCursor();
      return true;
    } catch ( PDOException $e ) {
      throw new QueryException( 'Cannot save comment', $e );
    }
    return false;
  }

  public function setContent( $value ) {
    $this->content = $value;
  }

  public function setEditedCount( $value ) {
    $this->edited_count = $value;
  }

  public function setEditedDateTime( \DateTime $value ) {
    $this->edited_datetime = $value->format( 'Y-m-d H:i:s' );
  }

}
