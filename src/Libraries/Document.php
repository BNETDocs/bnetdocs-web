<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DateTimeImmutable;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \DateTimeInterface;
use \DateTimeZone;
use \OutOfBoundsException;
use \Parsedown;
use \StdClass;

class Document implements \BNETDocs\Interfaces\DatabaseObject, \JsonSerializable
{
  // Maximum SQL field lengths, alter as appropriate
  public const MAX_BRIEF = 0xFF;
  public const MAX_CONTENT = 0xFFFFFF;
  public const MAX_EDITED_COUNT = 0x7FFFFFFFFFFFFFFF;
  public const MAX_ID = 0x7FFFFFFFFFFFFFFF;
  public const MAX_OPTIONS = 0x7FFFFFFFFFFFFFFF;
  public const MAX_TITLE = 0xFF;
  public const MAX_USER_ID = 0x7FFFFFFFFFFFFFFF;

  public const OPTION_MARKDOWN  = 0x00000001; // Markdown-formatted brief and content
  public const OPTION_PUBLISHED = 0x00000002; // 'Draft' badge and visiblility to non-editors
  public const DEFAULT_OPTION = self::OPTION_MARKDOWN;

  protected string $brief;
  protected string $content;
  protected DateTimeInterface $created_datetime;
  protected int $edited_count;
  protected ?DateTimeInterface $edited_datetime;
  protected ?int $id;
  protected int $options;
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
      if (!$this->allocate()) throw new \BNETDocs\Exceptions\DocumentNotFoundException($this);
    }
  }

  /**
   * Allocates the properties of this object from the database.
   *
   * @return boolean Whether the operation was successful.
   */
  public function allocate() : bool
  {
    $this->setBrief('');
    $this->setContent('');
    $this->setCreatedDateTime(new DateTimeImmutable('now'));
    $this->setEditedCount(0);
    $this->setEditedDateTime(null);
    $this->setOptions(self::DEFAULT_OPTION);
    $this->setTitle('');
    $this->setUserId(null);

    $id = $this->getId();
    if (is_null($id)) return true;

    $q = Database::instance()->prepare('
      SELECT
        `brief`,
        `content`,
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `title`,
        `user_id`
      FROM `documents` WHERE `id` = ? LIMIT 1;
    ');
    if (!$q || !$q->execute([$id]) || $q->rowCount() != 1) return false;
    $this->allocateObject($q->fetchObject());
    $q->closeCursor();
    return true;
  }

  /**
   * Internal function to process and translate StdClass objects into properties.
   */
  private function allocateObject(StdClass $value) : void
  {
    $this->setBrief($value->brief);
    $this->setContent($value->content);
    $this->setCreatedDateTime($value->created_datetime);
    $this->setEditedCount($value->edited_count);
    $this->setEditedDateTime($value->edited_datetime);
    $this->setId($value->id);
    $this->setOptions($value->options_bitmask);
    $this->setTitle($value->title);
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
      INSERT INTO `documents` (
        `brief`,
        `content`,
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `title`,
        `user_id`
      ) VALUES (
        :b, :c, :cdt, :ec, :edt, :id, :o, :t, :uid
      ) ON DUPLICATE KEY UPDATE
        `brief` = :b,
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
      ':b' => $this->getBrief(false),
      ':c' => $this->getContent(false),
      ':cdt' => $this->getCreatedDateTime(),
      ':ec' => $this->getEditedCount(),
      ':edt' => $this->getEditedDateTime(),
      ':id' => $this->getId(),
      ':o' => $this->getOptions(),
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
    $q = Database::instance()->prepare('DELETE FROM `documents` WHERE `id` = ? LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { if ($q) $q->closeCursor(); }
  }

  public static function getAllDocuments(?array $order = null, bool $published_only = false) : array|false
  {
    $q = Database::instance()->prepare(sprintf('
      SELECT
        `brief`,
        `content`,
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `title`,
        `user_id`
      FROM `documents`%s ORDER BY %s;
    ', $published_only ? sprintf(' WHERE (`options_bitmask` & %d) = %d', self::OPTION_PUBLISHED, self::OPTION_PUBLISHED) : '', (
      $order ? (sprintf('`%s` %s, `id` %s', $order[0], $order[1], $order[1])) : '`id` ASC'
    )));
    if (!$q || !$q->execute()) return false;

    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  public function getBrief(bool $format) : string
  {
    if (!($format && $this->isMarkdown())) return $this->brief;

    $md = new Parsedown();
    $md->setBreaksEnabled(true);
    return $md->text($this->brief);
  }

  public function getContent(bool $format) : string
  {
    if (!($format && $this->isMarkdown())) return $this->content;

    $md = new Parsedown();
    $md->setBreaksEnabled(true);
    return $md->text($this->content);
  }

  public function getCreatedDateTime() : ?DateTimeInterface
  {
    return $this->created_datetime;
  }

  public static function getDocumentsByLastEdited(int $count) : array|false
  {
    $q = Database::instance()->prepare(sprintf('
      SELECT
        `brief`,
        `content`,
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `title`,
        `user_id`
      FROM `documents`
      ORDER BY IFNULL(`edited_datetime`, `created_datetime`) DESC
      LIMIT %d;
    ', $count));
    if (!$q || !$q->execute()) return false;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  public static function getDocumentsByUserId(int $user_id) : array|false
  {
    $q = Database::instance()->prepare('
      SELECT
        `brief`,
        `content`,
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `title`,
        `user_id`
      FROM `documents` WHERE `user_id` = ? ORDER BY `id` ASC;
    ');
    if (!$q || !$q->execute([$user_id])) return false;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
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
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_OPTIONS
      ));
    }

    return ($this->options & $option) === $option;
  }

  public function getOptions() : int
  {
    return $this->options;
  }

  public function getPublishedDateTime() : ?DateTimeInterface
  {
    return (!is_null($this->edited_datetime) ?
      $this->getEditedDateTime() : $this->getCreatedDateTime()
    );
  }

  public function getTitle() : string
  {
    return $this->title;
  }

  public function getURI() : ?string
  {
    $id = $this->getId();
    if (is_null($id)) return $id;
    return Common::relativeUrlToAbsolute(sprintf('/document/%d/%s', $id, Common::sanitizeForUrl($this->getTitle(), true)));
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

  public function jsonSerialize() : mixed
  {
    return [
      'brief' => $this->getBrief(false),
      'content' => $this->getContent(false),
      'created_datetime' => $this->getCreatedDateTime(),
      'edited_count' => $this->getEditedCount(),
      'edited_datetime' => $this->getEditedDateTime(),
      'id' => $this->getId(),
      'options_bitmask' => $this->getOptions(),
      'title' => $this->getTitle(),
      'user_id' => $this->getUserId(),
    ];
  }

  /**
   * Sets the brief description of this document.
   *
   * @param string $value The brief description.
   * @throws OutOfBoundsException if value length is not between zero and MAX_BRIEF.
   */
  public function setBrief(string $value) : void
  {
    if (strlen($value) > self::MAX_BRIEF)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d characters', self::MAX_BRIEF
      ));
    }

    $this->brief = $value;
  }

  /**
   * Sets the content of this document.
   * 
   * @param string $value The content.
   * @throws OutOfBoundsException if value length is not between zero and MAX_CONTENT.
   */
  public function setContent(string $value) : void
  {
    if (strlen($value) > self::MAX_CONTENT)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d characters', self::MAX_CONTENT
      ));
    }

    $this->content = $value;
  }

  /**
   * Sets the Date and Time this Document was created.
   *
   * @param DateTimeInterface|string $value The Date and Time value.
   */
  public function setCreatedDateTime(DateTimeInterface|string $value) : void
  {
    $this->created_datetime = (is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value
    );
  }

  /**
   * Sets the number of times this Document has been modified.
   *
   * @param int $value The total number of modifications.
   * @throws OutOfBoundsException if value is not between zero and MAX_EDITED_COUNT.
   */
  public function setEditedCount(int $value) : void
  {
    if ($value < 0 || $value > self::MAX_EDITED_COUNT)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_EDITED_COUNT
      ));
    }

    $this->edited_count = $value;
  }

  /**
   * Sets the Date and Time that this Document was last modified, or null for not yet.
   *
   * @param DateTimeInterface|string|null $value The Date and Time value.
   */
  public function setEditedDateTime(DateTimeInterface|string|null $value) : void
  {
    $this->edited_datetime = (is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value
    );
  }

  /**
   * Sets the database id for this Document object.
   *
   * @param ?int $value The database id for this Document object. When set to null, calling commit() will
   *                    get a new id, however until then the Document will not have a valid webpage/URI.
   * @throws OutOfBoundsException if value is not between zero and MAX_ID, or null.
   */
  public function setId(?int $value) : void
  {
    if (!is_null($value) && ($value < 0 || $value > self::MAX_ID))
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_ID
      ));
    }

    $this->id = $value;
  }

  /**
   * Toggles the Markdown-format option, which alters how brief and content are parsed and printed.
   *
   * @param bool @value If true, value is passed into the Parsedown class before being printed.
   *                    If false, value is *not* passed into Parsedown before being printed.
   */
  public function setMarkdown(bool $value) : void
  {
    $this->setOption(self::OPTION_MARKDOWN, $value);
  }

  /**
   * Alters one option out of the options bitmask for this Document.
   *
   * @param int $option The option to change to the value.
   * @param bool $value Changes option to true (1) or false (0) based on this value.
   * @throws OutOfBoundsException if option is not between zero and MAX_OPTIONS.
   */
  public function setOption(int $option, bool $value) : void
  {
    if ($option < 0 || $option > self::MAX_OPTIONS)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_OPTIONS
      ));
    }

    if ($value)
    {
      $this->options |= $option; // bitwise or
    }
    else
    {
      $this->options &= ~$option; // bitwise and ones complement
    }
  }

  /**
   * Sets the options bitmask for this Document.
   *
   * @param int $value The full set of options which will replace previous options.
   * @throws OutOfBoundsException if value is not between zero and MAX_OPTIONS.
   */
  public function setOptions(int $value) : void
  {
    if ($value < 0 || $value > self::MAX_OPTIONS)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_OPTIONS
      ));
    }

    $this->options = $value;
  }

  /**
   * Toggles draft status and visibility to non-editor user accounts.
   *
   * @param bool @value If true, enables 'Draft' badge and disables visibility to non-editors.
   *                    If false, disables 'Draft' badge and enables visibility to everyone.
   */
  public function setPublished(bool $value) : void
  {
    $this->setOption(self::OPTION_PUBLISHED, $value);
  }

  /**
   * Sets the title for this Document object.
   * 
   * @param string $value The title.
   * @throws OutOfBoundsException if value length is not between zero and MAX_TITLE.
   */
  public function setTitle(string $value) : void
  {
    if (strlen($value) > self::MAX_TITLE)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d characters', self::MAX_TITLE
      ));
    }

    $this->title = $value;
  }

  /**
   * Set the user this Document was created by.
   *
   * @param ?User $value The User (object) that created this Document (object), or null for no user.
   * @throws OutOfBoundsException if value (User) id is not between zero and MAX_USER_ID, or null.
   */
  public function setUser(?User $value) : void
  {
    $this->setUserId($value ? $value->getId() : $value);
  }

  /**
   * Set the user this Document was created by.
   *
   * @param ?int $value The User (id) that created this Document (object), or null for no user.
   * @throws OutOfBoundsException if value is not between zero and MAX_USER_ID, or null.
   */
  public function setUserId(?int $value) : void
  {
    if (!is_null($value) && ($value < 0 || $value > self::MAX_USER_ID))
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_USER_ID
      ));
    }

    $this->user_id = $value;
  }
}
