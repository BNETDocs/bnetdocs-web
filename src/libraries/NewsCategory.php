<?php
namespace BNETDocs\Libraries;

use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\NewsCategoryNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \CarlBennett\MVC\Libraries\Common;
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
      $objects = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $objects[] = new self($row);
      }
      $stmt->closeCursor();
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
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh news category", $e);
    }
    return false;
  }

}
