<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DateTimeImmutable;
use \BNETDocs\Libraries\Packet\Application as ApplicationLayer;
use \BNETDocs\Libraries\Packet\Transport as TransportLayer;
use \BNETDocs\Libraries\Product;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \DateTimeInterface;
use \DateTimeZone;
use \InvalidArgumentException;
use \OutOfBoundsException;
use \Parsedown;
use \StdClass;
use \UnexpectedValueException;

class Packet implements \BNETDocs\Interfaces\DatabaseObject, \JsonSerializable
{
  public const DEFAULT_APPLICATION_LAYER_ID = 1; // SID
  public const DEFAULT_DIRECTION = self::DIRECTION_CLIENT_SERVER;
  public const DEFAULT_OPTION = self::OPTION_MARKDOWN;
  public const DEFAULT_TRANSPORT_LAYER_ID = 1; // TCP

  public const DIRECTION_CLIENT_SERVER = 1; // Client to Server
  public const DIRECTION_SERVER_CLIENT = 2; // Server to Client
  public const DIRECTION_PEER_TO_PEER  = 3; // Peer to Peer

  // Maximum SQL field lengths, alter as appropriate
  public const MAX_APPLICATION_LAYER_ID = 0x7FFFFFFFFFFFFFFF;
  public const MAX_BRIEF = 0xFF;
  public const MAX_DIRECTION = 0x7FFFFFFFFFFFFFFF;
  public const MAX_EDITED_COUNT = 0x7FFFFFFFFFFFFFFF;
  public const MAX_FORMAT = 0xFFFF;
  public const MAX_ID = 0x7FFFFFFFFFFFFFFF;
  public const MAX_NAME = 0xFF;
  public const MAX_OPTIONS = 0x7FFFFFFFFFFFFFFF;
  public const MAX_PACKET_ID = 0xFF;
  public const MAX_REMARKS = 0xFFFFFF;
  public const MAX_TRANSPORT_LAYER_ID = 0x7FFFFFFFFFFFFFFF;
  public const MAX_USER_ID = 0x7FFFFFFFFFFFFFFF;

  public const OPTION_MARKDOWN   = 0x00000001; // Markdown-formatted remarks
  public const OPTION_PUBLISHED  = 0x00000002; // 'Draft' badge and visiblility to non-editors
  public const OPTION_DEPRECATED = 0x00000004; // 'Deprecated' badge
  public const OPTION_RESEARCH   = 0x00000008; // 'In Research' badge

  protected int $application_layer_id;
  protected string $brief;
  protected ?DateTimeInterface $created_datetime;
  protected int $direction;
  protected int $edited_count;
  protected ?DateTimeInterface $edited_datetime;
  protected string $format;
  protected ?int $id;
  protected string $name;
  protected int $options;
  protected int $packet_id;
  protected string $remarks;
  protected int $transport_layer_id;
  protected array $used_by;
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
      if (!$this->allocate()) throw new \BNETDocs\Exceptions\PacketNotFoundException($this);
    }
  }

  /**
   * Allocates the properties of this object from the database.
   *
   * @return boolean Whether the operation was successful.
   */
  public function allocate() : bool
  {
    $this->setApplicationLayerId(self::DEFAULT_APPLICATION_LAYER_ID);
    $this->setBrief('');
    $this->setCreatedDateTime(new DateTime('now'));
    $this->setDirection(self::DEFAULT_DIRECTION);
    $this->setEditedCount(0);
    $this->setEditedDateTime(null);
    $this->setFormat('[blank]');
    $this->setName('', true);
    $this->setOptions(self::DEFAULT_OPTION);
    $this->setPacketId(0);
    $this->setRemarks('');
    $this->setTransportLayerId(self::DEFAULT_TRANSPORT_LAYER_ID);
    $this->setUsedBy([]);
    $this->setUserId(null);

    $id = $this->getId();
    if (is_null($id)) return true;

    $q = Database::instance()->prepare('
      SELECT
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `packet_application_layer_id`,
        `packet_brief`,
        `packet_direction_id`,
        `packet_format`,
        `packet_id`,
        `packet_name`,
        `packet_remarks`,
        `packet_transport_layer_id`,
        `user_id`
      FROM `packets` WHERE `id` = ? LIMIT 1;
    ');
    if (!$q || !$q->execute([$id]) || $q->rowCount() != 1) return false;
    $r = $q->fetchObject();
    $q->closeCursor();

    $q = Database::instance()->prepare('
      SELECT `u`.`bnet_product_id` AS `used_by` FROM `packet_used_by` AS `u`
      INNER JOIN `products` AS `p` ON `u`.`bnet_product_id` = `p`.`bnet_product_id`
      WHERE `u`.`id` = ? ORDER BY `p`.`sort` ASC;
    ');
    if (!$q || !$q->execute([$id])) return false;

    $r->used_by = [];
    while ($row = $q->fetchObject()) $r->used_by[] = new Product((int) $row->used_by);
    $q->closeCursor();

    $this->allocateObject($r);
    return true;
  }

  /**
   * Internal function to process and translate StdClass objects into properties.
   */
  protected function allocateObject(StdClass $value)
  {
    $this->setApplicationLayerId($value->packet_application_layer_id);
    $this->setBrief($value->packet_brief);
    $this->setCreatedDateTime($value->created_datetime);
    $this->setDirection($value->packet_direction_id);
    $this->setEditedCount($value->edited_count);
    $this->setEditedDateTime($value->edited_datetime);
    $this->setFormat($value->packet_format);
    $this->setId($value->id);
    $this->setName($value->packet_name);
    $this->setOptions($value->options_bitmask);
    $this->setPacketId($value->packet_id);
    $this->setRemarks($value->packet_remarks);
    $this->setTransportLayerId($value->packet_transport_layer_id);
    $this->setUsedBy($value->used_by);
    $this->setUserId($value->user_id);
  }

  /**
   * Implements the commit function from the IDatabaseObject interface
   */
  public function commit() : bool
  {
    $q = Database::instance()->prepare('
      INSERT INTO `packets` (
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `packet_application_layer_id`,
        `packet_brief`,
        `packet_direction_id`,
        `packet_format`,
        `packet_id`,
        `packet_name`,
        `packet_remarks`,
        `packet_transport_layer_id`,
        `user_id`
      ) VALUES (
        :cdt, :ec, :edt, :id, :o, :app_id, :b, :d, :f, :pid, :n, :r, :tr_id, :uid
      ) ON DUPLICATE KEY UPDATE
        `created_datetime` = :cdt,
        `edited_count` = :ec,
        `edited_datetime` = :edt,
        `id` = :id,
        `options_bitmask` = :o,
        `packet_application_layer_id` = :app_id,
        `packet_brief` = :b,
        `packet_direction_id` = :d,
        `packet_format` = :f,
        `packet_id` = :pid,
        `packet_name` = :n,
        `packet_remarks` = :r,
        `packet_transport_layer_id` = :tr_id,
        `user_id` = :uid;
    ');

    $p = [
      ':app_id' => $this->getApplicationLayerId(),
      ':b' => $this->getBrief(false),
      ':cdt' => $this->getCreatedDateTime(),
      ':d' => $this->getDirection(),
      ':ec' => $this->getEditedCount(),
      ':edt' => $this->getEditedDateTime(),
      ':f' => $this->getFormat(),
      ':id' => $this->getId(),
      ':n' => $this->getName(),
      ':o' => $this->getOptions(),
      ':pid' => $this->getPacketId(false),
      ':r' => $this->getRemarks(false),
      ':tr_id' => $this->getTransportLayerId(),
      ':uid' => $this->getUserId(),
    ];

    foreach ($p as $k => &$v)
      if ($v instanceof DateTimeInterface)
        $p[$k] = $v->format(self::DATE_SQL);

    if (!$q || !$q->execute($p)) return false;
    $q->closeCursor();
    if (is_null($p[':id'])) $this->setId(Database::instance()->lastInsertId());
    $id = $this->getId();

    $q = Database::instance()->prepare('DELETE FROM `packet_used_by` WHERE `id` = :id;');
    if (!$q || !$q->execute([':id' => $id])) return false;

    $q = Database::instance()->prepare('INSERT INTO `packet_used_by` (`id`, `bnet_product_id`) VALUES (:i, :p);');
    foreach ($this->used_by as $v)
    {
      if (!$v) continue;
      $p = [
        ':i' => $id,
        ':p' => $v->getBnetProductId(),
      ];
      if (!$q || !$q->execute($p)) return false;
    }

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
    $q = Database::instance()->prepare('DELETE FROM `packets` WHERE `id` = ? LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { $q->closeCursor(); }
  }

  public static function &getAllPackets(?string $where_clause = null, ?array $order = null, ?int $limit = null, ?int $index = null) : array|false
  {
    if (!empty($where_clause))
    {
      $where_clause = 'WHERE ' . $where_clause;
    }

    if (!(is_numeric($limit) || is_numeric($index)))
    {
      $limit_clause = '';
    }
    else if (!is_numeric($index))
    {
      $limit_clause = 'LIMIT ' . (int) $limit;
    }
    else
    {
      $limit_clause = 'LIMIT ' . (int) $index . ',' . (int) $limit;
    }

    $order_clause =
      ( $order ? '`' .
        implode( '`,`', explode( ',', $order[0] )) .
        '` ' . $order[1] . ',' : ''
      ) . '`id` ' . ( $order ? $order[1] : 'ASC' ) . ' ' .
      $limit_clause
    ;

    $q = Database::instance()->prepare(sprintf('SELECT `id` FROM `packets` %s ORDER BY %s;', $where_clause, $order_clause));
    if (!$q || !$q->execute()) return false;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self((int) $row->id);
    $q->closeCursor();
    return $r;
  }

  public static function getPacketsByLastEdited(int $count) : array|false
  {
    $q = Database::instance()->prepare(sprintf('
      SELECT `id` FROM `packets`
      ORDER BY IFNULL(`edited_datetime`, `created_datetime`) DESC
      LIMIT %d;
    ', $count));
    if (!$q || !$q->execute()) return false;

    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self((int) $row->id);
    $q->closeCursor();
    return $r;
  }

  public function getApplicationLayer() : ApplicationLayer
  {
    return new ApplicationLayer($this->application_layer_id);
  }

  public function getApplicationLayerId() : int
  {
    return $this->application_layer_id;
  }

  public function getBrief(bool $format) : string
  {
    if (!($format && $this->getOption(self::OPTION_MARKDOWN))) return $this->brief;

    $md = new Parsedown();
    $md->setBreaksEnabled(true);
    return $md->text($this->brief);
  }

  public function getCreatedDateTime() : DateTimeInterface
  {
    return $this->created_datetime;
  }

  public function getDirection() : int
  {
    return $this->direction;
  }

  public function getDirectionLabel() : string
  {
    switch ($this->direction)
    {
      case self::DIRECTION_CLIENT_SERVER: return 'Client to Server';
      case self::DIRECTION_SERVER_CLIENT: return 'Server to Client';
      case self::DIRECTION_PEER_TO_PEER:  return 'Peer to Peer';
      default: throw new UnexpectedValueException(sprintf(
        'packet direction: %d is invalid', $this->direction
      ));
    }
  }

  public function getDirectionTag() : string
  {
    switch ($this->direction)
    {
      case self::DIRECTION_CLIENT_SERVER: return 'C>S';
      case self::DIRECTION_SERVER_CLIENT: return 'S>C';
      case self::DIRECTION_PEER_TO_PEER:  return 'P2P';
      default: throw new UnexpectedValueException(sprintf(
        'packet direction: %d is invalid', $this->direction
      ));
    }
  }

  public function getEditedCount() : int
  {
    return $this->edited_count;
  }

  public function getEditedDateTime() : ?DateTimeInterface
  {
    return $this->edited_datetime;
  }

  public function getFormat() : string
  {
    return $this->format;
  }

  public function getId() : ?int
  {
    return $this->id;
  }

  public function getLabel() : string
  {
    return sprintf('%s %s %s',
      $this->getDirectionTag(),
      $this->getPacketId(true),
      $this->getName()
    );
  }

  public function getName() : string
  {
    return $this->name;
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

  public function getPacketId(bool $format = false) : int|string
  {
    if (!$format) return $this->packet_id;
    return sprintf('0x%02X', $this->packet_id); // Prints a value like "0xFF"
  }

  public function getRemarks(bool $format) : string
  {
    if (!($format && $this->getOption(self::OPTION_MARKDOWN))) return $this->remarks;

    $md = new Parsedown();
    $md->setBreaksEnabled(true);
    return $md->text($this->remarks);
  }

  public function getTransportLayer() : TransportLayer
  {
    return new TransportLayer($this->transport_layer_id);
  }

  public function getTransportLayerId() : int
  {
    return $this->transport_layer_id;
  }

  public static function getPacketsByUserId(int $user_id) : array|false
  {
    $q = Database::instance()->prepare('SELECT `id` FROM `packets` WHERE `user_id` = ? ORDER BY `id` ASC;');
    if (!$q || !$q->execute([$user_id])) return false;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self((int) $row->id);
    $q->closeCursor();
    return $r;
  }

  public static function getPacketCount() : int|false
  {
    $q = Database::instance()->prepare('SELECT COUNT(*) AS `count` FROM `packets`;');
    if (!$q || !$q->execute() || $q->rowCount() == 0) return false;
    $r = $q->fetchObject();
    $q->closeCursor();
    return (int) $r->count;
  }

  public function getPublishedDateTime() : DateTimeInterface
  {
    return $this->getEditedDateTime() ?? $this->getCreatedDateTime();
  }

  public function getURI() : ?string
  {
    $id = $this->getId();
    if (is_null($id)) return null;
    return Common::relativeUrlToAbsolute(sprintf('/packet/%d/%s', $id, Common::sanitizeForUrl($this->getName(), true)));
  }

  public function getUsedBy() : array
  {
    return $this->used_by;
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

  public function isDeprecated() : bool
  {
    return $this->getOption(self::OPTION_DEPRECATED);
  }

  public function isInResearch() : bool
  {
    return $this->getOption(self::OPTION_RESEARCH);
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
      'created_datetime' => $this->getCreatedDateTime(),
      'edited_count' => $this->getEditedCount(),
      'edited_datetime' => $this->getEditedDateTime(),
      'id' => $this->getId(),
      'options_bitmask' => $this->getOptions(),
      'packet_application_layer_id' => $this->getApplicationLayerId(),
      'packet_brief' => $this->getBrief(false),
      'packet_direction_id' => $this->getDirection(),
      'packet_format' => $this->getFormat(),
      'packet_id' => $this->getPacketId(),
      'packet_name' => $this->getName(),
      'packet_remarks' => $this->getRemarks(false),
      'packet_transport_layer_id' => $this->getTransportLayerId(),
      'user_id' => $this->getUserId(),
    ];
  }

  /**
   * Sets the Application layer (SID/MCP/BNLS/etc.) associated with this Packet.
   *
   * @param int @value The application layer (id).
   */
  public function setApplicationLayerId(int $value) : void
  {
    if ($value < 0 || $value > self::MAX_APPLICATION_LAYER_ID)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_APPLICATION_LAYER_ID
      ));
    }

    $this->application_layer_id = $value;
  }

  /**
   * Sets the brief description of this packet.
   *
   * @param string $value The brief description.
   * @throws OutOfBoundsException if value is not between one and MAX_BRIEF.
   */
  public function setBrief(string $value) : void
  {
    if (strlen($value) > self::MAX_BRIEF)
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d characters', self::MAX_BRIEF
      ));

    $this->brief = $value;
  }

  /**
   * Sets the Date and Time this Packet was created.
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
   * Toggles the 'Deprecated' badge for this Packet.
   *
   * @param bool $value If true, the 'Deprecated' badge is visible. If false, it is not visible.
   */
  public function setDeprecated(bool $value) : void
  {
    $this->setOption(self::OPTION_DEPRECATED, $value);
  }

  /**
   * Sets the message direction for this Packet.
   *
   * @param int $value The message direction.
   * @throws UnexpectedValueException if value is invalid
   */
  public function setDirection(int $value) : void
  {
    switch ($value)
    {
      case self::DIRECTION_CLIENT_SERVER:
      case self::DIRECTION_SERVER_CLIENT:
      case self::DIRECTION_PEER_TO_PEER:
        break;
      default: throw new UnexpectedValueException(sprintf(
        'packet direction: %d is invalid', $value
      ));
    }

    $this->direction = $value;
  }

  /**
   * Sets the number of times this Packet has been modified.
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
   * Sets the Date and Time that this Packet was last modified, or null for not yet.
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
   * Sets the message format for this Packet. Values are typically multiline
   * and each line prefixed with a data-type such as '(UINT32) field name'.
   * The value is typically printed using a monospace font in a code block.
   *
   * @param string $value The message format.
   * @throws OutOfBoundsException if value length length is not between one and MAX_FORMAT.
   */
  public function setFormat(string $value) : void
  {
    if (strlen($value) < 1 || strlen($value) > self::MAX_FORMAT)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 1-%d characters', self::MAX_FORMAT
      ));
    }

    $this->format = $value;
  }

  /**
   * Sets the database id for this Packet object. Not to be confused with setPacketId().
   *
   * @param ?int $value The database id for this Packet object. When set to null, calling commit() will
   *                    get a new id, however until then the Packet will not have a valid webpage/URI.
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
   * Toggles the 'In Research' badge for this Packet.
   *
   * @param bool $value If true, the 'In Research' badge is visible. If false, it is not visible.
   */
  public function setInResearch(bool $value) : void
  {
    $this->setOption(self::OPTION_RESEARCH, $value);
  }

  /**
   * Toggles the Markdown-format option, which alters how brief and remarks are parsed and printed.
   *
   * @param bool @value If true, value is passed into the Parsedown class before being printed.
   *                    If false, value is *not* passed into Parsedown before being printed.
   */
  public function setMarkdown(bool $value) : void
  {
    $this->setOption(self::OPTION_MARKDOWN, $value);
  }

  /**
   * Alters one option out of the options bitmask for this Packet.
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
   * Sets the options bitmask for this Packet.
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
   * Sets the Packet name, e.g. 'SID_NULL'
   *
   * @param string @value The name of the Packet.
   * @param boolean $ignore_empty Whether an empty value should be accepted, defaults false.
   * @return void
   * @throws OutOfBoundsException if value must be between 1-MAX_NAME characters.
   */
  public function setName(string $value, bool $ignore_empty = false) : void
  {
    if ((!$ignore_empty && empty($value)) || strlen($value) > self::MAX_NAME)
      throw new OutOfBoundsException(sprintf(
        'value must be between 1-%d characters', self::MAX_NAME
      ));

    $this->name = $value;
  }

  /**
   * Sets the Packet/message id. Not to be confused with the database id, set using setId().
   *
   * @param mixed $value The message id. Supports binary, decimal, hexadecimal, and octal input formats.
   * @throws InvalidArgumentException if value cannot be translated into an integer.
   * @throws OutOfBoundsException if value is not between zero and MAX_PACKET_ID.
   */
  public function setPacketId($value) : void
  {
    if (is_string($value) && strlen($value) >= 2 && (
      substr($value, 0, 2) == '&b' || substr($value, 0, 2) == '&B'))
    {
      // Binary (&b1010011, &B1010011)
      $v = bindec(substr($value, 2));
    }
    else if (is_string($value) && strlen($value) >= 2 && (
      substr($value, 0, 2) == '0x' ||
      substr($value, 0, 2) == '&h' || substr($value, 0, 2) == '&H'))
    {
      // Hexadecimal (0x53, &h53, &H53)
      $v = hexdec(substr($value, 2));
    }
    else if (is_string($value) && strlen($value) >= 2 && (
      substr($value, 0, 2) == '&o' || substr($value, 0, 2) == '&O'))
    {
      // Octal (&o123, &O123)
      $v = octdec(substr($value, 2));
    }
    else if (is_string($value) && strlen($value) >= 1 && substr($value, 0, 1) == '0')
    {
      // Octal (0123)
      $v = octdec(substr($value, 1));
    }
    else if (is_numeric($value) && strpos($value, '.') === false)
    {
      // Decimal (123)
      $v = (int) $value;
    }
    else
    {
      throw new InvalidArgumentException(
        'value must be a binary, decimal, hexadecimal, or octal string or integer'
      );
    }

    if ($v < 0 || $v > self::MAX_PACKET_ID)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_PACKET_ID
      ));
    }

    $this->packet_id = $v;
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
   * Sets the remarks for this Packet.
   *
   * @param string $value The remarks.
   * @throws OutOfBoundsException if value length is greater than MAX_REMARKS.
   */
  public function setRemarks(string $value) : void
  {
    if (strlen($value) > self::MAX_REMARKS)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d characters', self::MAX_REMARKS
      ));
    }

    $this->remarks = $value;
  }

  /**
   * Sets the Transport layer (TCP/UDP/etc.) associated with this Packet.
   *
   * @param int @value The transport layer (id).
   * @throws OutOfBoundsException if value is not between zero and MAX_TRANSPORT_LAYER_ID.
   */
  public function setTransportLayerId(int $value) : void
  {
    if ($value < 0 || $value > self::MAX_TRANSPORT_LAYER_ID)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_TRANSPORT_LAYER_ID
      ));
    }

    $this->transport_layer_id = $value;
  }

  /**
   * Sets the products this Packet is used by.
   *
   * @param array $value The set of Product objects, or Product::$id integers.
   */
  public function setUsedBy(array $value) : void
  {
    $used_by = [];

    foreach ($value as $v)
    {
      $used_by[] = ($v instanceof Product ? $v : new Product($v));
    }

    $this->used_by = $used_by;
  }

  /**
   * Set the user this packet was created by.
   *
   * @param ?User $value The User (object) that created this packet, or null for no user.
   * @throws OutOfBoundsException if value (User) id is not between zero and MAX_USER_ID, or null.
   */
  public function setUser(?User $value) : void
  {
    $this->setUserId($value ? $value->getId() : $value);
  }

  /**
   * Set the user this packet was created by.
   *
   * @param ?int $value The User (id) that created this packet, or null for no user.
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
