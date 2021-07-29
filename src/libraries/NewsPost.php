<?php
namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\NewsPostNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\NewsCategory;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \Parsedown;
use \StdClass;

class NewsPost {

  const OPTION_MARKDOWN   = 0x00000001;
  const OPTION_PUBLISHED  = 0x00000002;
  const OPTION_RSS_EXEMPT = 0x00000004;

  protected $category_id;
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
      $this->category_id      = null;
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
      $this->category_id      = $data->category_id;
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
    $user_id, $category_id, $options_bitmask, $title, $content
  ) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $successful = false;
    try {
      $stmt = Common::$database->prepare("
        INSERT INTO `news_posts` (
          `id`, `created_datetime`, `edited_datetime`, `edited_count`,
          `user_id`, `category_id`, `options_bitmask`, `title`, `content`
        ) VALUES (
          NULL, NOW(), NULL, 0,
          :user_id, :category_id, :options_bitmask, :title, :content
        );
      ");
      $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
      $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
      $stmt->bindParam(":options_bitmask", $options_bitmask, PDO::PARAM_INT);
      $stmt->bindParam(":title", $title, PDO::PARAM_STR);
      $stmt->bindParam(":content", $content, PDO::PARAM_STR);
      $successful = $stmt->execute();
      $stmt->closeCursor();
    } catch (PDOException $e) {
      throw new QueryException("Cannot create news post", $e);
    } finally {
      //Credits::getTopContributorsByNewsPosts(true); // Refresh statistics
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
        DELETE FROM `news_posts` WHERE `id` = :id LIMIT 1;
      ");
      $stmt->bindParam(":id", $id, PDO::PARAM_INT);
      $successful = $stmt->execute();
      $stmt->closeCursor();
    } catch (PDOException $e) {
      throw new QueryException("Cannot delete news post", $e);
    } finally {
      //Credits::getTopContributorsByNewsPosts(true); // Refresh statistics
      return $successful;
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
          `created_datetime`,
          `edited_count`,
          `edited_datetime`,
          `id`,
          `options_bitmask`,
          `title`,
          `user_id`
        FROM `news_posts`
        ORDER BY
          `created_datetime`
          " . ($reverse ? "DESC" : "ASC") . ",
          `id`
          " . ($reverse ? "DESC" : "ASC") . "
        ;
      ");
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh news post");
      }
      $objects = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $objects[] = new self($row);
      }
      $stmt->closeCursor();
      return $objects;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh news post", $e);
    }
    return null;
  }

  public static function getNewsPostsByLastEdited(int $count)
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $stmt = Common::$database->prepare(
     'SELECT
        `category_id`,
        `content`,
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `title`,
        `user_id`
      FROM `news_posts`
      ORDER BY IFNULL(`edited_datetime`, `created_datetime`) DESC
      LIMIT ' . $count . ';'
    );

    $r = $stmt->execute();
    if (!$r)
    {
      throw new QueryException('Cannot query news posts by last edited');
    }

    $r = [];
    while ($row = $stmt->fetch(PDO::FETCH_OBJ))
    {
      $r[] = new self($row);
    }

    $stmt->closeCursor();
    return $r;
  }

  public static function getNewsPostsByUserId($user_id) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `category_id`,
          `content`,
          `created_datetime`,
          `edited_count`,
          `edited_datetime`,
          `id`,
          `options_bitmask`,
          `title`,
          `user_id`
        FROM `news_posts`
        WHERE `user_id` = :user_id
        ORDER BY `id` ASC;
      ");
      $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot query news posts by user id");
      }
      $objects = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $objects[] = new self($row);
      }
      $stmt->closeCursor();
      return $objects;
    } catch (PDOException $e) {
      throw new QueryException("Cannot query news posts by user id", $e);
    }
    return null;
  }

  public function getCategory() {
    return new NewsCategory($this->category_id);
  }

  public function getCategoryId() {
    return $this->category_id;
  }

  public function getContent(bool $render) {
    if (!$render) {
      return $this->content;
    }
    if ($this->options_bitmask & self::OPTION_MARKDOWN) {
      $md = new Parsedown();
      return $md->text($this->content);
    } else {
      return filter_var($this->content, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
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

  public function getMarkdown() {
    return $this->options_bitmask & self::OPTION_MARKDOWN;
  }

  public function getPublished() {
    return $this->options_bitmask & self::OPTION_PUBLISHED;
  }

  public function getRSSExempt() {
    return $this->options_bitmask & self::OPTION_RSS_EXEMPT;
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
      "/news/" . $this->getId() . "/" . Common::sanitizeForUrl(
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

  public function isMarkdown() {
    return ($this->options_bitmask & self::OPTION_MARKDOWN);
  }

  public function isPublished() {
    return ($this->options_bitmask & self::OPTION_PUBLISHED);
  }

  public function isRSSExempt() {
    return ($this->options_bitmask & self::OPTION_RSS_EXEMPT);
  }

  protected static function normalize(StdClass &$data) {
    $data->category_id      = (int)    $data->category_id;
    $data->content          = (string) $data->content;
    $data->created_datetime = (string) $data->created_datetime;
    $data->edited_count     = (int)    $data->edited_count;
    $data->id               = (int)    $data->id;
    $data->options_bitmask  = (int)    $data->options_bitmask;
    $data->title            = (string) $data->title;

    if (!is_null($data->edited_datetime))
      $data->edited_datetime = (string) $data->edited_datetime;

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
          `category_id`,
          `content`,
          `created_datetime`,
          `edited_count`,
          `edited_datetime`,
          `id`,
          `options_bitmask`,
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
      self::normalize($row);
      $this->category_id      = $row->category_id;
      $this->content          = $row->content;
      $this->created_datetime = $row->created_datetime;
      $this->edited_count     = $row->edited_count;
      $this->edited_datetime  = $row->edited_datetime;
      $this->options_bitmask  = $row->options_bitmask;
      $this->title            = $row->title;
      $this->user_id          = $row->user_id;
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh news post", $e);
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
          `news_posts`
        SET
          `category_id` = :category_id,
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
      $stmt->bindParam(":category_id", $this->category_id, PDO::PARAM_INT);
      $stmt->bindParam(":content", $this->content, PDO::PARAM_STR);
      $stmt->bindParam(":created_dt", $this->created_datetime, PDO::PARAM_STR);
      $stmt->bindParam(":edited_count", $this->edited_count, PDO::PARAM_INT);
      if (is_null($this->edited_datetime)) {
        $stmt->bindParam(":edited_dt", null, PDO::PARAM_NULL);
      } else {
        $stmt->bindParam(":edited_dt", $this->edited_datetime, PDO::PARAM_STR);
      }
      $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
      $stmt->bindParam(":options", $this->options_bitmask, PDO::PARAM_INT);
      $stmt->bindParam(":title", $this->title, PDO::PARAM_STR);
      if (is_null($this->user_id)) {
        $stmt->bindParam(":user_id", null, PDO::PARAM_NULL);
      } else {
        $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
      }
      if (!$stmt->execute()) {
        throw new QueryException("Cannot save news post");
      }
      $stmt->closeCursor();
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot save news post", $e);
    }
    return false;
  }

  public function setCategoryId($value) {
    $this->category_id = $value;
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

  public function setRSSExempt($value) {
    if ($value) {
      $this->options_bitmask |= self::OPTION_RSS_EXEMPT;
    } else {
      $this->options_bitmask &= ~self::OPTION_RSS_EXEMPT;
    }
  }

  public function setTitle($value) {
    $this->title = $value;
  }

}
