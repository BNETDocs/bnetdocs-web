<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\NewsCategoryNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Markdown;
use \BNETDocs\Libraries\User;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;

class NewsCategory {

  protected $filename;
  protected $id;
  protected $label;
  protected $sort_id;

  public function __construct($data) {
    if (is_numeric($data)) {
      $this->filename         = null;
      $this->id               = (int) $data;
      $this->label            = null;
      $this->sort_id          = null;
      $this->refresh();
    } else if ($data instanceof StdClass) {
      self::normalize($data);
      $this->filename         = $data->filename;
      $this->id               = $data->id;
      $this->label            = $data->label;
      $this->sort_id          = $data->sort_id;
    } else {
      throw new InvalidArgumentException("Cannot use data argument");
    }
  }

  public static function getAll() {
    $cache_key = "bnetdocs-newscategories";
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
          `filename`,
          `id`,
          `label`,
          `sort_id`
        FROM `news_categories`
        ORDER BY `id` ASC;
      ");
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh news categories");
      }
      $ids     = [];
      $objects = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $ids[]     = (int) $row->id;
        $objects[] = new self($row);
        Common::$cache->set(
          "bnetdocs-newscategory-" . $row->id, serialize($row), 300
        );
      }
      $stmt->closeCursor();
      Common::$cache->set($cache_key, implode(",", $ids), 300);
      return $objects;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh news categories", $e);
    }
    return null;
  }

  public function getFilename() {
    return $this->filename;
  }

  public function getId() {
    return $this->id;
  }

  public function getLabel() {
    return $this->label;
  }

  public function getSortId() {
    return $this->sort_id;
  }

  protected static function normalize(StdClass &$data) {
    $data->filename         = (string) $data->filename;
    $data->id               = (int)    $data->id;
    $data->label            = (string) $data->label;
    $data->sort_id          = (string) $data->sort_id;

    return true;
  }

  public function refresh() {
    $cache_key = "bnetdocs-newscategory-" . $this->id;
    $cache_val = Common::$cache->get($cache_key);
    if ($cache_val !== false) {
      $cache_val = unserialize($cache_val);
      $this->filename = $cache_val->filename;
      $this->label    = $cache_val->label;
      $this->sort_id  = $cache_val->sort_id;
      return true;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `filename`,
          `id`,
          `label`,
          `sort_id`
        FROM `news_categories`
        WHERE `id` = :id
        LIMIT 1;
      ");
      $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh news category");
      } else if ($stmt->rowCount() == 0) {
        throw new NewsCategoryNotFoundException($this->id);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      self::normalize($row);
      $this->filename = $row->filename;
      $this->label    = $row->label;
      $this->sort_id  = $row->sort_id;
      Common::$cache->set($cache_key, serialize($row), 300);
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh news category", $e);
    }
    return false;
  }

}
