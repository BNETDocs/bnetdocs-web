<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DateTimeImmutable;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \DateTimeInterface;
use \DateTimeZone;
use \StdClass;
use \UnexpectedValueException;

class Comment implements \BNETDocs\Interfaces\DatabaseObject, \JsonSerializable
{
  public const MAX_CONTENT      = 0xFFFF;
  public const MAX_EDITED_COUNT = 0xFFFFFFFFFFFFFFFF;
  public const MAX_ID           = 0xFFFFFFFFFFFFFFFF;
  public const MAX_PARENT_ID    = 0xFFFFFFFFFFFFFFFF;
  public const MAX_PARENT_TYPE  = 0xFFFFFFFFFFFFFFFF;
  public const MAX_USER_ID      = 0xFFFFFFFFFFFFFFFF;

  public const PARENT_TYPE_DOCUMENT  = 0;
  public const PARENT_TYPE_COMMENT   = 1;
  public const PARENT_TYPE_NEWS_POST = 2;
  public const PARENT_TYPE_PACKET    = 3;
  public const PARENT_TYPE_SERVER    = 4;
  public const PARENT_TYPE_USER      = 5;

  protected string $content;
  protected DateTimeInterface $created_datetime;
  protected int $edited_count;
  protected ?DateTimeInterface $edited_datetime;
  protected ?int $id;
  protected int $parent_id;
  protected int $parent_type;
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
      if (!$this->allocate()) throw new \BNETDocs\Exceptions\CommentNotFoundException($this);
    }
  }

  /**
   * Allocates the properties of this object from the database.
   *
   * @return boolean Whether the operation was successful.
   */
  public function allocate() : bool
  {
    $this->setContent('');
    $this->setCreatedDateTime('now');
    $this->setEditedCount(0);
    $this->setEditedDateTime(null);
    $this->setParentId(0);
    $this->setParentType(self::PARENT_TYPE_COMMENT);
    $this->setUserId(null);

    $id = $this->getId();
    if (is_null($id)) return true;

    $q = Database::instance()->prepare('
      SELECT
        `content`,
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `parent_id`,
        `parent_type`,
        `user_id`
      FROM `comments` WHERE `id` = ? LIMIT 1;
    ');
    if (!$q || !$q->execute([$id])) return false;
    $this->allocateObject($q->fetchObject());
    $q->closeCursor();
    return true;
  }

  protected function allocateObject(StdClass $value) : void
  {
    $this->setContent($value->content);
    $this->setCreatedDateTime($value->created_datetime);
    $this->setEditedCount($value->edited_count);
    $this->setEditedDateTime($value->edited_datetime);
    $this->setId($value->id);
    $this->setParentId($value->parent_id);
    $this->setParentType($value->parent_type);
    $this->setUserId($value->user_id);
  }

  /**
   * Commits the properties of this object to the database.
   *
   * @return boolean Whether the operation was successful.
   */
  public function commit() : bool
  {
    $q = Database::instance()->prepare('
      INSERT INTO `comments` (
        `content`,
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `parent_id`,
        `parent_type`,
        `user_id`
      ) VALUES (
        :content,
        :created_dt,
        :edited_count,
        :edited_dt,
        :id,
        :parent_id,
        :parent_type,
        :user_id
      ) ON DUPLICATE KEY UPDATE
        `content` = :content,
        `created_datetime` = :created_dt,
        `edited_count` = :edited_count,
        `edited_datetime` = :edited_dt,
        `parent_id` = :parent_id,
        `parent_type` = :parent_type,
        `user_id` = :user_id;
    ');

    $p = [
      ':content' => $this->getContent(false),
      ':created_dt' => $this->getCreatedDateTime(),
      ':edited_count' => $this->getEditedCount(),
      ':edited_dt' => $this->getEditedDateTime(),
      ':id' => $this->getId(),
      ':parent_id' => $this->getParentId(),
      ':parent_type' => $this->getParentType(),
      ':user_id' => $this->getUserId(),
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
    $q = Database::instance()->prepare('DELETE FROM `comments` WHERE `id` = ? LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { $q->closeCursor(); }
  }

  public static function getAll(int $parent_type, int $parent_id) : ?array
  {
    $q = Database::instance()->prepare('
      SELECT
        `content`,
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `parent_id`,
        `parent_type`,
        `user_id`
      FROM `comments` WHERE
        `parent_type` = :pt AND
        `parent_id` = :pid
      ORDER BY
        `created_datetime` ASC,
        `id` ASC;
    ');
    if (!$q || !$q->execute([':pid' => $parent_id, ':pt' => $parent_type])) return null;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  public static function getCommentsByUserId(int $user_id, bool $descending) : ?array
  {
    $o = $descending ? 'DESC' : 'ASC';
    $q = Database::instance()->prepare(sprintf('
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
      WHERE `user_id` = ? ORDER BY `created_datetime` %s, `id` %s;
    ', $o, $o));
    if (!$q || !$q->execute([$user_id])) return null;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  public function getContent(bool $format, bool $autobreak = true) : string
  {
    if (!$format) return $this->content;

    $md = new \Parsedown();
    $md->setBreaksEnabled($autobreak);
    $md->setSafeMode(true); // unsafe user-input
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

  public function getParentId() : int
  {
    return $this->parent_id;
  }

  public function getParentType() : int
  {
    return $this->parent_type;
  }

  public function getParentTypeCreatedEventId() : int
  {
    $pt = $this->getParentType();
    switch ($pt)
    {
      case Comment::PARENT_TYPE_DOCUMENT: $r = EventTypes::COMMENT_CREATED_DOCUMENT; break;
      case Comment::PARENT_TYPE_COMMENT: $r = EventTypes::COMMENT_CREATED_COMMENT; break;
      case Comment::PARENT_TYPE_NEWS_POST: $r = EventTypes::COMMENT_CREATED_NEWS; break;
      case Comment::PARENT_TYPE_PACKET: $r = EventTypes::COMMENT_CREATED_PACKET; break;
      case Comment::PARENT_TYPE_SERVER: $r = EventTypes::COMMENT_CREATED_SERVER; break;
      case Comment::PARENT_TYPE_USER: $r = EventTypes::COMMENT_CREATED_USER; break;
      default: throw new UnexpectedValueException(sprintf('Parent type (%d) unknown', $pt));
    }
    return $r;
  }

  public function getParentTypeEditedEventId() : int
  {
    $pt = $this->getParentType();
    switch ($pt)
    {
      case Comment::PARENT_TYPE_DOCUMENT: $r = EventTypes::COMMENT_EDITED_DOCUMENT; break;
      case Comment::PARENT_TYPE_COMMENT: $r = EventTypes::COMMENT_EDITED_COMMENT; break;
      case Comment::PARENT_TYPE_NEWS_POST: $r = EventTypes::COMMENT_EDITED_NEWS; break;
      case Comment::PARENT_TYPE_PACKET: $r = EventTypes::COMMENT_EDITED_PACKET; break;
      case Comment::PARENT_TYPE_SERVER: $r = EventTypes::COMMENT_EDITED_SERVER; break;
      case Comment::PARENT_TYPE_USER: $r = EventTypes::COMMENT_EDITED_USER; break;
      default: throw new UnexpectedValueException(sprintf('Parent type (%d) unknown', $pt));
    }
    return $r;
  }

  public function getParentTypeDeletedEventId() : int
  {
    $pt = $this->getParentType();
    switch ($pt)
    {
      case Comment::PARENT_TYPE_DOCUMENT: $r = EventTypes::COMMENT_DELETED_DOCUMENT; break;
      case Comment::PARENT_TYPE_COMMENT: $r = EventTypes::COMMENT_DELETED_COMMENT; break;
      case Comment::PARENT_TYPE_NEWS_POST: $r = EventTypes::COMMENT_DELETED_NEWS; break;
      case Comment::PARENT_TYPE_PACKET: $r = EventTypes::COMMENT_DELETED_PACKET; break;
      case Comment::PARENT_TYPE_SERVER: $r = EventTypes::COMMENT_DELETED_SERVER; break;
      case Comment::PARENT_TYPE_USER: $r = EventTypes::COMMENT_DELETED_USER; break;
      default: throw new UnexpectedValueException(sprintf('Parent type (%d) unknown', $pt));
    }
    return $r;
  }

  public function getParentUrl() : string|false
  {
    $pt = $this->getParentType();
    switch ($pt)
    {
      case self::PARENT_TYPE_DOCUMENT: $pts = 'document'; break;
      case self::PARENT_TYPE_COMMENT: $pts = 'comment'; break;
      case self::PARENT_TYPE_NEWS_POST: $pts = 'news'; break;
      case self::PARENT_TYPE_PACKET: $pts = 'packet'; break;
      case self::PARENT_TYPE_SERVER: $pts = 'server'; break;
      case self::PARENT_TYPE_USER: $pts = 'user'; break;
      default: throw new UnexpectedValueException(sprintf('Parent type (%d) unknown', $pt));
    }
    return Common::relativeUrlToAbsolute(sprintf('/%s/%d', $pts, $this->getParentId()));
  }

  public function getUser() : ?User
  {
    return is_null($this->user_id) ? null : new User($this->user_id);
  }

  public function getUserId() : ?int
  {
    return $this->user_id;
  }

  public function jsonSerialize() : mixed
  {
    return [
      'content' => $this->getContent(false),
      'created_datetime' => $this->getCreatedDateTime(),
      'edited_count' => $this->getEditedCount(),
      'edited_datetime' => $this->getEditedDateTime(),
      'id' => $this->getId(),
      'parent_id' => $this->getParentId(),
      'parent_type' => $this->getParentType(),
      'user_id' => $this->getUserId(),
    ];
  }

  public function incrementEdited() : void
  {
    $this->setEditedCount($this->getEditedCount() + 1);
    $this->setEditedDateTime(new DateTimeImmutable('now'));
  }

  public function setContent(string $value) : void
  {
    if (strlen($value) > self::MAX_CONTENT)
      throw new UnexpectedValueException(sprintf('value must be between 0-%d characters', self::MAX_CONTENT));

    $this->content = $value;
  }

  public function setCreatedDateTime(DateTimeInterface|string $value)
  {
    $this->created_datetime = (is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value
    );
  }

  public function setEditedCount(int $value) : void
  {
    if ($value < 0 || $value > self::MAX_EDITED_COUNT)
      throw new UnexpectedValueException(sprintf('value must be an integer between 0-%d', self::MAX_EDITED_COUNT));

    $this->edited_count = $value;
  }

  public function setEditedDateTime(DateTimeInterface|string|null $value)
  {
    $this->edited_datetime = (is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value
    );
  }

  public function setId(?int $value) : void
  {
    if ($value < 0 || $value > self::MAX_ID)
      throw new UnexpectedValueException(sprintf('value must be null or an integer between 0-%d', self::MAX_ID));

    $this->id = $value;
  }

  public function setParentId(int $value) : void
  {
    if ($value < 0 || $value > self::MAX_PARENT_ID)
      throw new UnexpectedValueException(sprintf('value must be an integer between 0-%d', self::MAX_PARENT_ID));

    $this->parent_id = $value;
  }

  public function setParentType(int $value) : void
  {
    if ($value < 0 || $value > self::MAX_PARENT_TYPE)
      throw new UnexpectedValueException(sprintf('value must be an integer between 0-%d', self::MAX_PARENT_TYPE));

    if (!self::validateParentType($value))
      throw new UnexpectedValueException('value must be a valid Comment::PARENT_TYPE_ constant');

    $this->parent_type = $value;
  }

  public function setUserId(?int $value) : void
  {
    if ($value < 0 || $value > self::MAX_USER_ID)
      throw new UnexpectedValueException(sprintf('value must be null or an integer between 0-%d', self::MAX_USER_ID));

    $this->user_id = $value;
  }

  public static function validateParentType(int $value) : bool
  {
    if ($value < 0 || $value > self::MAX_PARENT_TYPE)
      throw new UnexpectedValueException(sprintf('value must be an integer between 0-%d', self::MAX_PARENT_TYPE));

    switch ($value)
    {
      case self::PARENT_TYPE_COMMENT:
      case self::PARENT_TYPE_DOCUMENT:
      case self::PARENT_TYPE_NEWS_POST:
      case self::PARENT_TYPE_PACKET:
      case self::PARENT_TYPE_SERVER:
      case self::PARENT_TYPE_USER:
        return true;
      default: return false;
    }
  }
}
