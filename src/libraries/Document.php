<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\DocumentNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Markdown;
use \BNETDocs\Libraries\User;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;

class Document {

  const OPTION_MARKDOWN  = 0x00000001;
  const OPTION_PUBLISHED = 0x00000002;

  protected $content;
  protected $created_datetime;
  protected $edited_count;
  protected $edited_datetime;
  protected $id;
  protected $options_bitmask;
  protected $title;
  protected $user_id;

  public function __construct($data) {
    if (is_numeric($data)) {
      $this->content          = null;
      $this->created_datetime = null;
      $this->edited_count     = null;
      $this->edited_datetime  = null;
      $this->id               = (int) $data;
      $this->options_bitmask  = null;
      $this->title            = null;
      $this->user_id          = null;
      $this->refresh();
    } else if ($data instanceof StdClass) {
      self::normalize($data);
      $this->content          = $data->content;
      $this->created_datetime = $data->created_datetime;
      $this->edited_count     = $data->edited_count;
      $this->edited_datetime  = $data->edited_datetime;
      $this->id               = $data->id;
      $this->options_bitmask  = $data->options_bitmask;
      $this->title            = $data->title;
      $this->user_id          = $data->user_id;
    } else {
      throw new InvalidArgumentException("Cannot use data argument");
    }
  }

  public static function create(
    $user_id, $options_bitmask, $title, $content
  ) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $successful = false;
    try {
      $stmt = Common::$database->prepare("
        INSERT INTO `documents` (
          `id`, `created_datetime`, `edited_datetime`, `edited_count`,
          `user_id`, `options_bitmask`, `title`, `content`
        ) VALUES (
          NULL, NOW(), NULL, 0, :user_id, :options_bitmask, :title, :content
        );
      ");
      $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
      $stmt->bindParam(":options_bitmask", $options_bitmask, PDO::PARAM_INT);
      $stmt->bindParam(":title", $title, PDO::PARAM_STR);
      $stmt->bindParam(":content", $content, PDO::PARAM_STR);
      $successful = $stmt->execute();
      $stmt->closeCursor();
      if ($successful) Common::$cache->delete("bnetdocs-documents");
    } catch (PDOException $e) {
      throw new QueryException("Cannot create document", $e);
    } finally {
      //Credits::getTopContributorsByDocuments(true); // Refresh statistics
      return $successful;
    }
  }

  public static function delete($id) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $successful = false;
    try {
      $stmt = Common::$database->prepare("
        DELETE FROM `documents` WHERE `id` = :id LIMIT 1;
      ");
      $stmt->bindParam(":id", $id, PDO::PARAM_INT);
      $successful = $stmt->execute();
      $stmt->closeCursor();
      if ($successful) {
        Common::$cache->delete("bnetdocs-document-" . (int) $id);
        Common::$cache->delete("bnetdocs-documents");
      }
    } catch (PDOException $e) {
      throw new QueryException("Cannot delete document", $e);
    } finally {
      //Credits::getTopContributorsByNewsPosts(true); // Refresh statistics
      return $successful;
    }
  }

  public static function getAllDocuments() {
    $cache_key = "bnetdocs-documents";
    $cache_val = Common::$cache->get($cache_key);
    if ($cache_val !== false && !empty($cache_val)) {
      $ids     = explode(",", $cache_val);
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
          `content`,
          `created_datetime`,
          `edited_count`,
          `edited_datetime`,
          `id`,
          `options_bitmask`,
          `title`,
          `user_id`
        FROM `documents`
        ORDER BY `id` ASC;
      ");
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh documents");
      }
      $ids     = [];
      $objects = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $ids[]     = (int) $row->id;
        $objects[] = new self($row);
        Common::$cache->set(
          "bnetdocs-document-" . $row->id, serialize($row), 300
        );
      }
      $stmt->closeCursor();
      Common::$cache->set($cache_key, implode(",", $ids), 300);
      return $objects;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh documents", $e);
    }
    return null;
  }

  public function getContent($prepare) {
    if (!$prepare) {
      return $this->content;
    }
    if ($this->options_bitmask & self::OPTION_MARKDOWN) {
      $md = new Markdown();
      return $md->text($this->content);
    } else {
      return $this->content;
    }
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

  public static function getDocumentsByUserId($user_id) {
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
          `options_bitmask`,
          `title`,
          `user_id`
        FROM `documents`
        WHERE `user_id` = :user_id
        ORDER BY `id` ASC;
      ");
      $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot query documents by user id");
      }
      $documents = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $documents[] = new self($row);
        Common::$cache->set(
          "bnetdocs-document-" . $row->id, serialize($row), 300
        );
      }
      $stmt->closeCursor();
      return $documents;
    } catch (PDOException $e) {
      throw new QueryException("Cannot query documents by user id", $e);
    }
    return null;
  }

  public function getEditedCount() {
    return $this->edited_count;
  }

  public function getEditedDateTime() {
    if (is_null($this->edited_datetime)) {
      return $this->edited_datetime;
    } else {
      $tz = new DateTimeZone("UTC");
      $dt = new DateTime($this->edited_datetime);
      $dt->setTimezone($tz);
      return $dt;
    }
  }

  public function getId() {
    return $this->id;
  }

  public function getOptionsBitmask() {
    return $this->options_bitmask;
  }

  public function getPublishedDateTime() {
    if (!is_null($this->edited_datetime)) {
      return $this->getEditedDateTime();
    } else {
      return $this->getCreatedDateTime();
    }
  }

  public function getTitle() {
    return $this->title;
  }

  public function getURI() {
    return Common::relativeUrlToAbsolute(
      "/document/" . $this->getId() . "/" . Common::sanitizeForUrl(
        $this->getTitle(), true
      )
    );
  }

  public function getUser() {
    if (is_null($this->user_id)) return null;
    return new User($this->user_id);
  }

  public function getUserId() {
    return $this->user_id;
  }

  protected static function normalize(StdClass &$data) {
    $data->content          = (string) $data->content;
    $data->created_datetime = (string) $data->created_datetime;
    $data->edited_count     = (int)    $data->edited_count;
    $data->id               = (int)    $data->id;
    $data->options_bitmask  = (int)    $data->options_bitmask;
    $data->title            = (string) $data->title;

    if (!is_null($data->edited_datetime))
      $data->edited_datetime = $data->edited_datetime;

    if (!is_null($data->user_id))
      $data->user_id = $data->user_id;

    return true;
  }

  public function refresh() {
    $cache_key = "bnetdocs-document-" . $this->id;
    $cache_val = Common::$cache->get($cache_key);
    if ($cache_val !== false) {
      $cache_val              = unserialize($cache_val);
      $this->content          = $cache_val->content;
      $this->created_datetime = $cache_val->created_datetime;
      $this->edited_count     = $cache_val->edited_count;
      $this->edited_datetime  = $cache_val->edited_datetime;
      $this->options_bitmask  = $cache_val->options_bitmask;
      $this->title            = $cache_val->title;
      $this->user_id          = $cache_val->user_id;
      return true;
    }
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
          `options_bitmask`,
          `title`,
          `user_id`
        FROM `documents`
        WHERE `id` = :id
        LIMIT 1;
      ");
      $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh document");
      } else if ($stmt->rowCount() == 0) {
        throw new DocumentNotFoundException($this->id);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      self::normalize($row);
      $this->content          = $row->content;
      $this->created_datetime = $row->created_datetime;
      $this->edited_count     = $row->edited_count;
      $this->edited_datetime  = $row->edited_datetime;
      $this->id               = $row->id;
      $this->options_bitmask  = $row->options_bitmask;
      $this->title            = $row->title;
      $this->user_id          = $row->user_id;
      Common::$cache->set($cache_key, serialize($row), 300);
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh document", $e);
    }
    return false;
  }

  public function save() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        UPDATE
          `documents`
        SET
          `content` = :content,
          `created_datetime` = :created_dt,
          `edited_count` = :edited_count,
          `edited_datetime` = :edited_dt,
          `options_bitmask` = :options,
          `title` = :title,
          `user_id` = :user_id
        WHERE
          `id` = :id
        LIMIT 1;
      ");
      $stmt->bindParam(":content", $this->content, PDO::PARAM_INT);
      $stmt->bindParam(":created_dt", $this->created_datetime, PDO::PARAM_INT);
      $stmt->bindParam(":edited_count", $this->edited_count, PDO::PARAM_INT);
      $stmt->bindParam(":edited_dt", $this->edited_datetime, PDO::PARAM_INT);
      $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
      $stmt->bindParam(":options", $this->options_bitmask, PDO::PARAM_INT);
      $stmt->bindParam(":title", $this->title, PDO::PARAM_INT);
      $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot save document");
      }
      $stmt->closeCursor();

      $object                   = new StdClass();
      $object->content          = $this->content;
      $object->created_datetime = $this->created_datetime;
      $object->edited_count     = $this->edited_count;
      $object->edited_datetime  = $this->edited_datetime;
      $object->id               = $this->id;
      $object->options_bitmask  = $this->options_bitmask;
      $object->title            = $this->title;
      $object->user_id          = $this->user_id;

      $cache_key = "bnetdocs-document-" . $this->id;
      Common::$cache->set($cache_key, serialize($object), 300);
      Common::$cache->delete("bnetdocs-documents");

      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot save document", $e);
    }
    return false;
  }

  public function setContent($value) {
    $this->content = $value;
  }

  public function setEditedCount($value) {
    $this->edited_count = $value;
  }

  public function setEditedDateTime(\DateTime $value) {
    $this->edited_datetime = $value->format("Y-m-d H:i:s");
  }

  public function setMarkdown($value) {
    if ($value) {
      $this->options_bitmask |= self::OPTION_MARKDOWN;
    } else {
      $this->options_bitmask &= ~self::OPTION_MARKDOWN;
    }
  }

  public function setPublished($value) {
    if ($value) {
      $this->options_bitmask |= self::OPTION_PUBLISHED;
    } else {
      $this->options_bitmask &= ~self::OPTION_PUBLISHED;
    }
  }

  public function setTitle($value) {
    $this->title = $value;
  }

}
