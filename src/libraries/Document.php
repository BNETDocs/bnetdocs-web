<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\DocumentNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\IDatabaseObject;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \JsonSerializable;
use \OutOfBoundsException;
use \PDO;
use \PDOException;
use \Parsedown;
use \StdClass;
use \UnexpectedValueException;

class Document implements IDatabaseObject, JsonSerializable
{
  const DATE_SQL = 'Y-m-d H:i:s'; // DateTime::format() string for database

  const DEFAULT_OPTION = self::OPTION_MARKDOWN;

  // Maximum SQL field lengths, alter as appropriate
  const MAX_BRIEF = 0xFF;
  const MAX_CONTENT = 0xFFFFFF;
  const MAX_EDITED_COUNT = 0x7FFFFFFFFFFFFFFF;
  const MAX_ID = 0x7FFFFFFFFFFFFFFF;
  const MAX_OPTIONS = 0x7FFFFFFFFFFFFFFF;
  const MAX_TITLE = 0xFF;
  const MAX_USER_ID = 0x7FFFFFFFFFFFFFFF;

  const OPTION_MARKDOWN  = 0x00000001; // Markdown-formatted brief and content
  const OPTION_PUBLISHED = 0x00000002; // 'Draft' badge and visiblility to non-editors

  const TZ_SQL = 'Etc/UTC'; // database values are stored in this TZ

  private $_id;

  protected $brief;
  protected $content;
  protected $created_datetime;
  protected $edited_count;
  protected $edited_datetime;
  protected $id;
  protected $options;
  protected $title;
  protected $user_id;

  public function __construct($value)
  {
    if (is_string($value) && is_numeric($value) && strpos($value, '.') === false)
    {
      // something is lazily providing an int value in a string type
      $value = (int) $value;
    }

    if (is_null($value) || is_int($value))
    {
      $this->_id = $value;
      $this->allocate();
      return;
    }

    if ($value instanceof StdClass)
    {
      $this->allocateObject($value);
      return;
    }

    throw new InvalidArgumentException(sprintf(
      'value must be null, an integer, or StdClass; %s given', gettype($value)
    ));
  }

  /**
   * Implements the allocate function from the IDatabaseObject interface
   */
  public function allocate()
  {
    $id = $this->_id;

    if (!(is_null($id) || is_int($id)))
    {
      throw new InvalidArgumentException('value must be null or an integer');
    }

    $this->setBrief('');
    $this->setContent('');
    $this->setCreatedDateTime(new DateTime('now'));
    $this->setEditedCount(0);
    $this->setId($id);
    $this->setOptions(self::DEFAULT_OPTION);
    $this->setTitle('');

    if (is_null($id)) return;

    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare(
     'SELECT
        `brief`,
        `content`,
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `title`,
        `user_id`
      FROM `documents` WHERE `id` = :id LIMIT 1;'
    );
    $q->bindParam(':id', $id, PDO::PARAM_INT);

    $r = $q->execute();
    if (!$r)
    {
      throw new UnexpectedValueException(sprintf('an error occurred finding document id: %d', $id));
    }

    if ($q->rowCount() != 1)
    {
      throw new UnexpectedValueException(sprintf('document id: %d not found', $id));
    }

    $o = $q->fetchObject();
    $q->closeCursor();

    $this->allocateObject($o);
  }

  /**
   * Internal function to process and translate StdClass objects into properties.
   */
  protected function allocateObject(StdClass $value)
  {
    $tz = new DateTimeZone(self::TZ_SQL);

    $this->setBrief($value->brief);
    $this->setContent($value->content);
    $this->setCreatedDateTime(new DateTime($value->created_datetime, $tz));
    $this->setEditedCount($value->edited_count);
    $this->setEditedDateTime(
      $value->edited_datetime ? new DateTime($value->edited_datetime) : null
    );
    $this->setId($value->id);
    $this->setOptions($value->options_bitmask);
    $this->setTitle($value->title);
    $this->setUserId($value->user_id);
  }

  /**
   * Implements the commit function from the IDatabaseObject interface
   */
  public function commit()
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare(
      'INSERT INTO `documents` (
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
        `user_id` = :uid
    ;');

    $created_datetime = $this->created_datetime->format(self::DATE_SQL);

    $edited_datetime = (
      is_null($this->edited_datetime) ? null : $this->edited_datetime->format(self::DATE_SQL)
    );

    $q->bindParam(':b', $this->brief, (is_null($this->brief) ? PDO::PARAM_NULL : PDO::PARAM_STR));
    $q->bindParam(':c', $this->content, PDO::PARAM_STR);
    $q->bindParam(':cdt', $created_datetime, PDO::PARAM_STR);
    $q->bindParam(':ec', $this->edited_count, PDO::PARAM_INT);
    $q->bindParam(':edt', $edited_datetime, (is_null($edited_datetime) ? PDO::PARAM_NULL : PDO::PARAM_STR));
    $q->bindParam(':id', $this->id, (is_null($this->id) ? PDO::PARAM_NULL : PDO::PARAM_INT));
    $q->bindParam(':o', $this->options, PDO::PARAM_INT);
    $q->bindParam(':t', $this->title, PDO::PARAM_STR);
    $q->bindParam(':uid', $this->user_id, (is_null($this->user_id) ? PDO::PARAM_NULL : PDO::PARAM_INT));

    $r = $q->execute();
    if (!$r) return $r;

    if (is_null($this->id))
    {
      $this->setId(Common::$database->lastInsertId());
    }

    return $r;
  }

  public static function delete($id)
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $q = Common::$database->prepare('DELETE FROM `documents` WHERE `id` = :id LIMIT 1;');
    $q->bindParam(':id', $id, PDO::PARAM_INT);
    return $q->execute();
  }

  public static function getAllDocuments(?array $order = null)
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare(
     'SELECT `id` FROM `documents`
      ORDER BY
        ' . ($order ? '`' . $order[0] . '` ' . $order[1] . ',' : '') . '
        `id` ' . ($order ? $order[1] : 'ASC') . ';'
    );
    $r = $q->execute();
    if (!$r) return $r;

    $r = [];
    while ($row = $q->fetch(PDO::FETCH_NUM))
    {
      $r[] = new self($row[0]);
    }

    $q->closeCursor();
    return $r;
  }

  public function getBrief(bool $format)
  {
    if (!($format && $this->getOption(self::OPTION_MARKDOWN)))
    {
      return $this->brief;
    }

    $md = new Parsedown();
    return $md->text($this->brief);
  }

  public function getContent(bool $format)
  {
    if (!($format && $this->getOption(self::OPTION_MARKDOWN)))
    {
      return $this->content;
    }

    $md = new Parsedown();
    return $md->text($this->content);
  }

  public function getCreatedDateTime()
  {
    return $this->created_datetime;
  }

  public static function getDocumentsByLastEdited(int $count)
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare(sprintf(
     'SELECT `id` FROM `documents`
      ORDER BY IFNULL(`edited_datetime`, `created_datetime`) DESC
      LIMIT %d;', $count
    ));

    $r = $q->execute();
    if (!$r) return $r;

    $r = [];
    while ($row = $q->fetch(PDO::FETCH_NUM))
    {
      $r[] = new self($row[0]);
    }

    $q->closeCursor();
    return $r;
  }

  public static function getDocumentsByUserId(int $user_id)
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $q = Common::$database->prepare(
     'SELECT `id` FROM `documents` WHERE `user_id` = :id ORDER BY `id` ASC;'
    );
    $q->bindParam(':id', $user_id, PDO::PARAM_INT);
    $r = $q->execute();
    if (!$r) return $r;

    $r = [];
    while ($row = $q->fetch(PDO::FETCH_NUM))
    {
      $r[] = new self($row[0]);
    }

    $q->closeCursor();
    return $r;
  }

  public function getEditedCount()
  {
    return $this->edited_count;
  }

  public function getEditedDateTime()
  {
    return $this->edited_datetime;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getOption(int $option)
  {
    if ($option < 0 || $option > self::MAX_OPTIONS)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_OPTIONS
      ));
    }

    return ($this->options & $option) === $option;
  }

  public function getOptions()
  {
    return $this->options;
  }

  public function getPublishedDateTime()
  {
    return (!is_null($this->edited_datetime) ?
      $this->getEditedDateTime() : $this->getCreatedDateTime()
    );
  }

  public function getTitle()
  {
    return $this->title;
  }

  public function getURI()
  {
    $id = $this->getId();
    if (is_null($id)) return $id;
    return Common::relativeUrlToAbsolute(sprintf('/document/%d/%s', $id, Common::sanitizeForUrl($this->getTitle(), true)));
  }

  public function getUser()
  {
    return (is_null($this->user_id) ? $this->user_id : new User($this->user_id));
  }

  public function getUserId()
  {
    return $this->user_id;
  }

  public function incrementEdited()
  {
    $this->setEditedCount($this->getEditedCount() + 1);
    $this->setEditedDateTime(new DateTime('now'));
  }

  public function isMarkdown()
  {
    return $this->getOption(self::OPTION_MARKDOWN);
  }

  public function isPublished()
  {
    return $this->getOption(self::OPTION_PUBLISHED);
  }

  public function jsonSerialize()
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
      'user' => $this->getUser(),
    ];
  }

  /**
   * Sets the brief description of this document.
   *
   * @param string $value The brief description.
   * @throws OutOfBoundsException if value length is not between zero and MAX_BRIEF.
   */
  public function setBrief(string $value)
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
  public function setContent(string $value)
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
   * @param DateTime $value The DateTime object.
   */
  public function setCreatedDateTime(DateTime $value)
  {
    $this->created_datetime = $value;
  }

  /**
   * Sets the number of times this Document has been modified.
   *
   * @param int $value The total number of modifications.
   * @throws OutOfBoundsException if value is not between zero and MAX_EDITED_COUNT.
   */
  public function setEditedCount(int $value)
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
   * Sets the Date and Time that this Document was last modified.
   *
   * @param ?DateTime $value The last modified DateTime, or null for not modified yet.
   */
  public function setEditedDateTime(?DateTime $value) {
    $this->edited_datetime = $value;
  }

  /**
   * Sets the database id for this Document object.
   *
   * @param ?int $value The database id for this Document object. When set to null, calling commit() will
   *                    get a new id, however until then the Document will not have a valid webpage/URI.
   * @throws OutOfBoundsException if value is not between zero and MAX_ID, or null.
   */
  public function setId(?int $value)
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
  public function setMarkdown(bool $value)
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
  public function setOption(int $option, bool $value)
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
  public function setOptions(int $value)
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
  public function setPublished(bool $value)
  {
    $this->setOption(self::OPTION_PUBLISHED, $value);
  }

  /**
   * Sets the title for this Document object.
   * 
   * @param string $value The title.
   * @throws OutOfBoundsException if value length is not between zero and MAX_TITLE.
   */
  public function setTitle(string $value)
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
  public function setUser(?User $value)
  {
    $this->setUserId($value ? $value->getId() : $value);
  }

  /**
   * Set the user this Document was created by.
   *
   * @param ?int $value The User (id) that created this Document (object), or null for no user.
   * @throws OutOfBoundsException if value is not between zero and MAX_USER_ID, or null.
   */
  public function setUserId(?int $value)
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
