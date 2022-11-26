<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DateTimeImmutable;
use \BNETDocs\Libraries\NewsCategory;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \DateTimeInterface;
use \DateTimeZone;
use \OutOfBoundsException;
use \Parsedown;
use \StdClass;

class NewsPost implements \BNETDocs\Interfaces\DatabaseObject, \JsonSerializable
{
  public const MAX_CATEGORY_ID = 0xFFFFFFFFFFFFFFFF;
  public const MAX_CONTENT = 0xFFFFFF;
  public const MAX_EDITED_COUNT = 0xFFFFFFFF;
  public const MAX_ID = 0xFFFFFFFFFFFFFFFF;
  public const MAX_OPTIONS = 0xFFFFFFFFFFFFFFFF;
  public const MAX_TITLE = 0xFF;
  public const MAX_USER_ID = 0xFFFFFFFFFFFFFFFF;

  public const OPTION_MARKDOWN = 0x00000001;
  public const OPTION_PUBLISHED = 0x00000002;
  public const OPTION_RSS_EXEMPT = 0x00000004;

  public const DEFAULT_OPTION = self::OPTION_MARKDOWN | self::OPTION_RSS_EXEMPT;

  protected int $category_id;
  protected string $content;
  protected DateTimeInterface $created_datetime;
  protected int $edited_count;
  protected ?DateTimeInterface $edited_datetime;
  protected ?int $id;
  protected int $options_bitmask;
  protected string $title;
  protected ?int $user_id;

  public function __construct(StdClass|int|null $value)
  {
    if ($value instanceof StdClass)
    {
      $this->allocateObject($value);
    }
    else
    {
      $this->setId($value);
      if (!$this->allocate()) throw new \BNETDocs\Exceptions\NewsPostNotFoundException($this);
    }
  }

  public function allocate() : bool
  {
    $this->setCategoryId(0);
    $this->setContent('');
    $this->setCreatedDateTime(new DateTimeImmutable('now'));
    $this->setEditedCount(0);
    $this->setEditedDateTime(null);
    $this->setOptionsBitmask(self::DEFAULT_OPTION);
    $this->setTitle('');
    $this->setUserId(null);

    $id = $this->getId();
    if (is_null($id)) return true;

    $q = Database::instance()->prepare('
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
      FROM `news_posts` WHERE `id` = ? LIMIT 1;
    ');
    if (!$q || !$q->execute([$id]) || $q->rowCount() == 0) return false;
    $this->allocateObject($q->fetchObject());
    $q->closeCursor();
    return true;
  }

  protected function allocateObject(StdClass $value) : void
  {
    $this->setCategoryId($value->category_id);
    $this->setContent($value->content);
    $this->setCreatedDateTime($value->created_datetime);
    $this->setEditedCount($value->edited_count);
    $this->setEditedDateTime($value->edited_datetime);
    $this->setId($value->id);
    $this->setOptionsBitmask($value->options_bitmask);
    $this->setTitle($value->title);
    $this->setUserId($value->user_id);
  }

  public function commit() : bool
  {
    $q = Database::instance()->prepare('
      INSERT INTO `news_posts` (
        `category_id`,
        `content`,
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `title`,
        `user_id`
      ) VALUES (
        :cid, :c, :cdt, :ec, :edt, :id, :o, :t, :uid
      ) ON DUPLICATE KEY UPDATE
        `category_id` = :cid,
        `content` = :c,
        `created_datetime` = :cdt,
        `edited_count` = :ec,
        `edited_datetime` = :edt,
        `id` = :id,
        `options_bitmask` = :o,
        `title` = :t,
        `user_id` = :uid;
    ');

    $p = [
      ':cid' => $this->getCategoryId(),
      ':c' => $this->getContent(false),
      ':cdt' => $this->getCreatedDateTime(),
      ':ec' => $this->getEditedCount(),
      ':edt' => $this->getEditedDateTime(),
      ':id' => $this->getId(),
      ':o' => $this->getOptionsBitmask(),
      ':t' => $this->getTitle(),
      ':uid' => $this->getUserId(),
    ];

    foreach ($p as $k => $v)
      if ($v instanceof DateTimeInterface)
        $p[$k] = $v->format(self::DATE_SQL);

    if (!$q || !$q->execute($p)) return false;
    if (is_null($p[':id'])) $this->setId(Database::instance()->lastInsertId());
    $q->closeCursor();
    return true;
  }

  /**
   * Deallocates the properties of this object from the database.
   *
   * @return boolean Whether the operation was successful.
   */
  public function deallocate() : bool
  {
    $id = $this->getId();
    if (is_null($id)) return false;
    $q = Database::instance()->prepare('DELETE FROM `news_posts` WHERE `id` = ? LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { $q->closeCursor(); }
  }

  public static function getAllNews(bool $reverse) : ?array
  {
    $o = $reverse ? 'DESC' : 'ASC';
    $q = Database::instance()->prepare(sprintf('
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
      FROM `news_posts` ORDER BY `created_datetime` %s, `id` %s;
    ', $o, $o));
    if (!$q || !$q->execute()) return null;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  public static function getNewsPostsByLastEdited(int $limit) : ?array
  {
    $q = Database::instance()->prepare(sprintf('
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
      FROM `news_posts` ORDER BY IFNULL(`edited_datetime`, `created_datetime`) DESC LIMIT %d;
    ', $limit));
    if (!$q || !$q->execute()) return null;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  public static function getNewsPostsByUserId(int $user_id) : ?array
  {
    $q = Database::instance()->prepare('
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
      FROM `news_posts` WHERE `user_id` = ? ORDER BY `id` ASC;
    ');
    if (!$q || !$q->execute([$user_id])) return null;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  public function getCategory() : NewsCategory
  {
    return new NewsCategory($this->category_id);
  }

  public function getCategoryId() : int
  {
    return $this->category_id;
  }

  public function getContent(bool $format) : string
  {
    if (!$format) return $this->content;
    if (!$this->isMarkdown()) return filter_var($this->content, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $md = new Parsedown();
    $md->setBreaksEnabled(true);
    return $md->text($this->content);
  }

  public function getCreatedDateTime() : DateTimeInterface
  {
    return $this->created_datetime;
  }

  public function getEditedCount() : int
  {
    return $this->edited_count;
  }

  public function getEditedDateTime() : ?DateTimeInterface
  {
    return $this->edited_datetime;
  }

  public function getId() : ?int
  {
    return $this->id;
  }

  public function getOption(int $option) : bool
  {
    if ($option < 0 || $option > self::MAX_OPTIONS)
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_OPTIONS
      ));

    return ($this->options_bitmask & $option) === $option;
  }

  public function getOptionsBitmask() : int
  {
    return $this->options_bitmask;
  }

  public function getPublishedDateTime() : DateTimeInterface
  {
    return $this->getEditedDateTime() ?? $this->getCreatedDateTime();
  }

  public function getTitle() : string
  {
    return $this->title;
  }

  public function getURI() : string
  {
    return Common::relativeUrlToAbsolute(sprintf(
      '/news/%d/%s', $this->getId(), Common::sanitizeForUrl($this->getTitle(), true)
    ));
  }

  public function getUser() : ?User
  {
    if (is_null($this->user_id)) return null;
    try { return new User($this->user_id); }
    catch (\UnexpectedValueException) { return null; }
  }

  public function getUserId() : ?int
  {
    return $this->user_id;
  }

  public function incrementEdited() : void
  {
    $this->setEditedCount($this->getEditedCount() + 1);
    $this->setEditedDateTime(new DateTimeImmutable('now'));
  }

  public function isMarkdown() : bool
  {
    return $this->getOption(self::OPTION_MARKDOWN);
  }

  public function isPublished() : bool
  {
    return $this->getOption(self::OPTION_PUBLISHED);
  }

  public function isRSSExempt() : bool
  {
    return $this->getOption(self::OPTION_RSS_EXEMPT);
  }

  public function jsonSerialize(): mixed
  {
    return [
      'category' => $this->getCategory(),
      'content' => $this->getContent(false),
      'created_datetime' => $this->getCreatedDateTime(),
      'edited_count' => $this->getEditedCount(),
      'edited_datetime' => $this->getEditedDateTime(),
      'id' => $this->getId(),
      'options_bitmask' => $this->getOptionsBitmask(),
      'title' => $this->getTitle(),
      'user_id' => $this->getUserId(),
    ];
  }

  public function setCategoryId($value) : void
  {
    if ($value < 0 || $value > self::MAX_CATEGORY_ID)
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_CATEGORY_ID
      ));

    $this->category_id = $value;
  }

  public function setCreatedDateTime(DateTimeInterface|string $value) : void
  {
    $this->created_datetime = (is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value
    );
  }

  public function setContent($value) : void
  {
    if (strlen($value) > self::MAX_CONTENT)
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d characters', self::MAX_CONTENT
      ));

    $this->content = $value;
  }

  public function setEditedCount($value) : void
  {
    if ($value < 0 || $value > self::MAX_EDITED_COUNT)
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_EDITED_COUNT
      ));

    $this->edited_count = $value;
  }

  public function setEditedDateTime(DateTimeInterface|string|null $value) : void
  {
    $this->edited_datetime = (is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value
    );
  }

  public function setId(?int $value) : void
  {
    if (!is_null($value) && ($value < 0 || $value > self::MAX_ID))
      throw new OutOfBoundsException(sprintf(
        'value must be null or between 0-%d', self::MAX_ID
      ));

    $this->id = $value;
  }

  public function setOption(int $option, bool $value) : void
  {
    if ($option < 0 || $option > self::MAX_OPTIONS)
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_OPTIONS
      ));

    if ($value) $this->options_bitmask |= $option; // bitwise or
    else $this->options_bitmask &= ~$option; // bitwise and ones complement
  }

  public function setOptionsBitmask(int $value) : void
  {
    if ($value < 0 || $value > self::MAX_OPTIONS)
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_OPTIONS
      ));

    $this->options_bitmask = $value;
  }

  public function setMarkdown(bool $value) : void
  {
    $this->setOption(self::OPTION_MARKDOWN, $value);
  }

  public function setPublished(bool $value) : void
  {
    $this->setOption(self::OPTION_PUBLISHED, $value);
  }

  public function setRSSExempt(bool $value) : void
  {
    $this->setOption(self::OPTION_RSS_EXEMPT, $value);
  }

  public function setTitle(string $value) : void
  {
    if (strlen($value) > self::MAX_TITLE)
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d characters', self::MAX_TITLE
      ));

    $this->title = $value;
  }

  public function setUserId(?int $value) : void
  {
    if ($value < 0 || $value > self::MAX_USER_ID)
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_USER_ID
      ));

    $this->user_id = $value;
  }
}
