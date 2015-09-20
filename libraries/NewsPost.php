<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\NewsPostNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Markdown;
use \BNETDocs\Libraries\User;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;

class NewsPost {

  const OPTION_MARKDOWN  = 1;
  const OPTION_PUBLISHED = 2;

  protected $category_id;
  protected $content;
  protected $edit_count;
  protected $edit_date;
  protected $id;
  protected $options_bitmask;
  protected $post_date;
  protected $title;
  protected $user_id;

  public function __construct($data) {
    if (is_numeric($data)) {
      $this->category_id     = null;
      $this->content         = null;
      $this->edit_count      = null;
      $this->edit_date       = null;
      $this->id              = (int)$data;
      $this->options_bitmask = null;
      $this->post_date       = null;
      $this->title           = null;
      $this->user_id         = null;
      $this->refresh();
    } else if ($data instanceof StdClass) {
      $this->category_id     = $data->category-id;
      $this->content         = $data->content;
      $this->edit_count      = $data->edit_count;
      $this->edit_date       = $data->edit_date;
      $this->id              = $data->id;
      $this->options_bitmask = $data->options_bitmask;
      $this->post_date       = $data->post_date;
      $this->title           = $data->title;
      $this->user_id         = $data->user_id;
    } else {
      throw new InvalidArgumentException("Cannot use data argument");
    }
  }

  public static function getAllNews($reverse) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `category_id`,
          `content`,
          `edit_count`,
          `edit_date`,
          `id`,
          `options_bitmask`,
          `post_date`,
          `title`,
          `user_id`
        FROM `news_posts`
        ORDER BY `id` " . ($reverse ? "DESC" : "ASC") . ";
      ");
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh news post");
      } else if ($stmt->rowCount() == 0) {
        throw new NewsPostNotFoundException(null);
      }
      $news_posts = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $news_posts[] = new self($row);
        Common::$cache->set(
          "bnetdocs-newspost-" . $row->id, serialize($row), 300
        );
      }
      $stmt->closeCursor();
      return $news_posts;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh news post", $e);
    }
    return null;
  }

  public function getCategoryId() {
    return $this->category_id;
  }

  public function getContent($prepare) {
    if (!$prepare) {
      return $this->content;
    }
    if ($this->options_bitmask & self::OPTION_MARKDOWN) {
      $md = new Markdown();
      return $md->text($this->content);
    } else {
      return htmlspecialchars($this->content, ENT_HTML5, "UTF-8");
    }
  }

  public function getEditCount() {
    return $this->edit_count;
  }

  public function getEditDate() {
    if (is_null($this->edit_date)) {
      return $this->edit_date;
    } else {
      $tz = new DateTimeZone("UTC");
      $dt = new DateTime($this->edit_date);
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

  public function getPostDate() {
    if (is_null($this->post_date)) {
      return $this->post_date;
    } else {
      $tz = new DateTimeZone("UTC");
      $dt = new DateTime($this->post_date);
      $dt->setTimezone($tz);
      return $dt;
    }
  }

  public function getPublishDate() {
    if (!is_null($this->edit_date)) {
      return $this->getEditDate();
    } else {
      return $this->getPostDate();
    }
  }

  public function getTitle() {
    return $this->title;
  }

  public function getUser() {
    return new User($this->user_id);
  }

  public function getUserId() {
    return $this->user_id;
  }

  public function refresh() {
    $cache_key = "bnetdocs-newspost-" . $this->id;
    $cache_val = Common::$cache->get($cache_key);
    if ($cache_val !== false) {
      $cache_val = unserialize($cache_val);
      $this->category_id     = $cache_val->category_id;
      $this->content         = $cache_val->content;
      $this->edit_count      = $cache_val->edit_count;
      $this->edit_date       = $cache_val->edit_date;
      $this->options_bitmask = $cache_val->options_bitmask;
      $this->post_date       = $cache_val->post_date;
      $this->title           = $cache_val->title;
      $this->user_id         = $cache_val->user_id;
      return true;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `category_id`,
          `content`,
          `edit_count`,
          `edit_date`,
          `id`,
          `options_bitmask`,
          `post_date`,
          `title`,
          `user_id`
        FROM `news_posts`
        WHERE `id` = :id
        LIMIT 1;
      ");
      $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh news post");
      } else if ($stmt->rowCount() == 0) {
        throw new NewsPostNotFoundException($this->id);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      $this->category_id     = $row->category_id;
      $this->content         = $row->content;
      $this->edit_count      = $row->edit_count;
      $this->edit_date       = $row->edit_date;
      $this->options_bitmask = $row->options_bitmask;
      $this->post_date       = $row->post_date;
      $this->title           = $row->title;
      $this->user_id         = $row->user_id;
      Common::$cache->set($cache_key, serialize($row), 300);
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh news post", $e);
    }
    return false;
  }

}
