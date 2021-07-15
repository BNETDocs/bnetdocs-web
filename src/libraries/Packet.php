<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\PacketNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\IDatabaseObject;
use \BNETDocs\Libraries\Packet\Application as ApplicationLayer;
use \BNETDocs\Libraries\Packet\Transport as TransportLayer;
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

class Packet implements IDatabaseObject, JsonSerializable
{
  const DATE_SQL = 'Y-m-d H:i:s'; // DateTime::format() string for database

  const DEFAULT_APPLICATION_LAYER_ID = 1;
  const DEFAULT_DIRECTION = self::DIRECTION_CLIENT_SERVER;
  const DEFAULT_OPTION = self::OPTION_MARKDOWN;
  const DEFAULT_TRANSPORT_LAYER_ID = 1;

  const DIRECTION_CLIENT_SERVER = 1;
  const DIRECTION_SERVER_CLIENT = 2;
  const DIRECTION_PEER_TO_PEER  = 3;

  // Maximum SQL field lengths, alter as appropriate
  const MAX_APPLICATION_LAYER_ID = 0xFFFFFFFFFFFFFFFF;
  const MAX_EDITED_COUNT = 0xFFFFFFFFFFFFFFFF;
  const MAX_FORMAT = 0xFFFF;
  const MAX_ID = 0xFFFFFFFFFFFFFFFF;
  const MAX_NAME = 191;
  const MAX_OPTIONS = 0xFFFFFFFFFFFFFFFF;
  const MAX_PACKET_ID = 0xFF;
  const MAX_REMARKS = 0xFFFF;
  const MAX_TRANSPORT_LAYER_ID = 0xFFFFFFFFFFFFFFFF;
  const MAX_USER_ID = 0xFFFFFFFFFFFFFFFF;

  const OPTION_MARKDOWN   = 0x00000001; // Markdown-formatted remarks
  const OPTION_PUBLISHED  = 0x00000002; // 'Draft' badge and visiblility to non-editors
  const OPTION_DEPRECATED = 0x00000004; // 'Deprecated' badge
  const OPTION_RESEARCH   = 0x00000008; // 'In Research' badge

  const TZ_SQL = 'Etc/UTC'; // database values are stored in this TZ

  private $_id;

  protected $application_layer_id;
  protected $created_datetime;
  protected $direction;
  protected $edited_count;
  protected $edited_datetime;
  protected $format;
  protected $id;
  protected $name;
  protected $options;
  protected $packet_id;
  protected $remarks;
  protected $transport_layer_id;
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

    $this->setApplicationLayerId(self::DEFAULT_APPLICATION_LAYER_ID);
    $this->setCreatedDateTime(new DateTime('now'));
    $this->setDirection(self::DEFAULT_DIRECTION);
    $this->setEditedCount(0);
    $this->setFormat('[blank]');
    $this->setId($id);
    $this->setOptions(self::DEFAULT_OPTION);
    $this->setPacketId(0);
    $this->setRemarks('');
    $this->setTransportLayerId(self::DEFAULT_TRANSPORT_LAYER_ID);

    if (is_null($id)) return;

    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      SELECT
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `packet_application_layer_id`,
        `packet_direction_id`,
        `packet_format`,
        `packet_id`,
        `packet_name`,
        `packet_remarks`,
        `packet_transport_layer_id`,
        `user_id`
      FROM `packets` WHERE `id` = :id LIMIT 1;
    ');
    $q->bindParam(':id', $id, PDO::PARAM_INT);

    $r = $q->execute();
    if (!$r)
    {
      throw new UnexpectedValueException(sprintf('an error occurred finding packet id: %d', $id));
    }

    if ($q->rowCount() != 1)
    {
      throw new UnexpectedValueException(sprintf('packet id: %d not found', $id));
    }

    $r = $q->fetchObject();
    $q->closeCursor();

    $this->allocateObject($r);
  }

  /**
   * Internal function to process and translate StdClass objects into properties.
   */
  protected function allocateObject(StdClass $value)
  {
    $tz = new DateTimeZone(self::TZ_SQL);

    $this->setApplicationLayerId($value->packet_application_layer_id);
    $this->setCreatedDateTime(new DateTime($value->created_datetime, $tz));
    $this->setDirection($value->packet_direction_id);
    $this->setEditedCount($value->edited_count);
    $this->setEditedDateTime(
      $value->edited_datetime ? new DateTime($value->edited_datetime) : null
    );
    $this->setFormat($value->packet_format);
    $this->setId($value->id);
    $this->setName($value->packet_name);
    $this->setOptions($value->options_bitmask);
    $this->setPacketId($value->packet_id);
    $this->setRemarks($value->packet_remarks);
    $this->setTransportLayerId($value->packet_transport_layer_id);
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
      'INSERT INTO `packets` (
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `packet_application_layer_id`,
        `packet_direction_id`,
        `packet_format`,
        `packet_id`,
        `packet_name`,
        `packet_remarks`,
        `packet_transport_layer_id`,
        `user_id`
      ) VALUES (
        :c_dt, :e_c, :e_dt, :id, :opts, :app_id, :id, :f, :pid, :n, :r, :tr_id, :uid
      ) ON DUPLICATE KEY UPDATE
        `created_datetime` = :c_dt,
        `edited_count` = :e_c,
        `edited_datetime` = :e_dt,
        `id` = :id,
        `options_bitmask` = :opts,
        `packet_application_layer_id` = :app_id,
        `packet_direction_id` = :d,
        `packet_format` = :f,
        `packet_id` = :pid,
        `packet_name` = :n,
        `packet_remarks` = :r,
        `packet_transport_layer_id` = :tr_id,
        `user_id` = :uid
      ;
      SELECT LAST_INSERT_ID();
    ');

    $created_datetime = $this->created_datetime->format(self::DATE_SQL);

    $edited_datetime = (
      is_null($this->edited_datetime) ? null : $this->edited_datetime->format(self::DATE_SQL)
    );

    $q->bindParam(':app_id', $this->application_layer_id, PDO::PARAM_INT);
    $q->bindParam(':c_dt', $created_datetime, PDO::PARAM_STR);
    $q->bindParam(':d', $this->direction, PDO::PARAM_INT);
    $q->bindParam(':e_c', $this->edited_count, PDO::PARAM_INT);
    $q->bindParam(':e_dt', $edited_datetime, (is_null($edited_datetime) ? PDO::PARAM_NULL : PDO::PARAM_STR));
    $q->bindParam(':f', $this->format, PDO::PARAM_STR);
    $q->bindParam(':id', $this->id, (is_null($this->id) ? PDO::PARAM_NULL : PDO::PARAM_INT));
    $q->bindParam(':n', $this->name, PDO::PARAM_STR);
    $q->bindParam(':opts', $this->options, PDO::PARAM_INT);
    $q->bindParam(':pid', $this->packet_id, PDO::PARAM_INT);
    $q->bindParam(':r', $this->remarks, PDO::PARAM_STR);
    $q->bindParam(':tr_id', $this->transport_layer_id, PDO::PARAM_INT);
    $q->bindParam(':uid', $this->user_id, (is_null($this->user_id) ? PDO::PARAM_NULL : PDO::PARAM_INT));

    $r = $q->execute();
    if (!$r) return $r;

    $q->nextRowset();
    $this->setId($q->fetch(PDO::FETCH_NUM)[0]);

    $q->closeCursor();
    return $r;
  }

  public static function delete(int $id)
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('DELETE FROM `packets` WHERE `id` = :id LIMIT 1;');
    $q->bindParam(':id', $id, PDO::PARAM_INT);
    return $q->execute();
  }

  public static function &getAllPackets(?string $where_clause = null, ?array $order = null, ?int $limit = null, ?int $index = null)
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

    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare(sprintf('
      SELECT
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `packet_application_layer_id`,
        `packet_direction_id`,
        `packet_format`,
        `packet_id`,
        `packet_name`,
        `packet_remarks`,
        `packet_transport_layer_id`,
        `user_id`
      FROM `packets` %s ORDER BY %s;', $where_clause, $order_clause
    ));

    $r = $q->execute();
    if (!$r) return $r;

    $r = [];
    while ($row = $q->fetch(PDO::FETCH_OBJ))
    {
      $r[] = new self($row);
    }

    $q->closeCursor();
    return $r;
  }

  public static function getPacketsByLastEdited(int $count)
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare(sprintf('
      SELECT
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `packet_application_layer_id`,
        `packet_direction_id`,
        `packet_format`,
        `packet_id`,
        `packet_name`,
        `packet_remarks`,
        `packet_transport_layer_id`,
        `user_id`
      FROM `packets`
      ORDER BY IFNULL(`edited_datetime`, `created_datetime`) DESC LIMIT %s;', $count
    ));

    $r = $q->execute();
    if (!$r) return $r;

    $r = [];
    while ($row = $q->fetch(PDO::FETCH_OBJ))
    {
      $r[] = new self($row);
    }

    $q->closeCursor();
    return $r;
  }

  public function getApplicationLayer()
  {
    return new ApplicationLayer($this->application_layer_id);
  }

  public function getApplicationLayerId()
  {
    return $this->application_layer_id;
  }

  public function getCreatedDateTime()
  {
    return $this->created_datetime;
  }

  public function getDirection()
  {
    return $this->direction;
  }

  public function getDirectionLabel()
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

  public function getDirectionTag()
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

  public function getEditedCount()
  {
    return $this->edited_count;
  }

  public function getEditedDateTime()
  {
    return $this->edited_datetime;
  }

  public function getFormat()
  {
    return $this->format;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getLabel()
  {
    return sprintf('%s %s %s',
      $this->getDirectionTag(),
      $this->getPacketId(true),
      $this->getName()
    );
  }

  public function getName()
  {
    return $this->name;
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

  public function getPacketId(bool $format = false)
  {
    if (!$format)
    {
      return $this->packet_id;
    }

    // Prints a value like "0xFF":
    return sprintf('0x%02X', $this->packet_id);
  }

  public function getRemarks(bool $format)
  {
    if (!($format && $this->getOption(self::OPTION_MARKDOWN)))
    {
      return $this->remarks;
    }

    $md = new Parsedown();
    return $md->text($this->remarks);
  }

  public function getTransportLayer()
  {
    return new TransportLayer( $this->transport_layer_id );
  }

  public function getTransportLayerId()
  {
    return $this->transport_layer_id;
  }

  public static function getPacketsByUserId(int $user_id)
  {
    if (!isset( Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      SELECT
        `created_datetime`,
        `edited_count`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `packet_application_layer_id`,
        `packet_direction_id`,
        `packet_format`,
        `packet_id`,
        `packet_name`,
        `packet_remarks`,
        `packet_transport_layer_id`,
        `user_id`
      FROM `packets`
      WHERE `user_id` = :user_id
      ORDER BY `id` ASC;
    ');
    $q->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    $r = $q->execute();
    if (!$r) return $r;

    $r = [];
    while ($row = $q->fetch(PDO::FETCH_OBJ))
    {
      $r[] = new self( $row );
    }

    $q->closeCursor();
    return $r;
  }

  public static function getPacketCount()
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('SELECT COUNT(*) FROM `packets`;');

    $r = $q->execute();
    if (!$r || $q->rowCount() === 0) return $r;

    $r = $q->fetch(PDO::FETCH_NUM);
    $q->closeCursor();
    return (int) $r[0];
  }

  public function getPublishedDateTime()
  {
    return $this->getEditedDateTime() ?? $this->getCreatedDateTime();
  }

  public function getURI()
  {
    return Common::relativeUrlToAbsolute(sprintf('/packet/%d/%s', $this->getId(), Common::sanitizeForUrl($this->getName(), true)));
  }

  public function getUsedBy()
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      SELECT `u`.`bnet_product_id` FROM `packet_used_by` AS `u`
      INNER JOIN `products` AS `p` ON `u`.`bnet_product_id` = `p`.`bnet_product_id`
      WHERE `u`.`id` = :id ORDER BY `p`.`sort` ASC;
    ');
    $q->bindParam(':id', $this->id, PDO::PARAM_INT);

    $r = $q->execute();
    if (!$r) return $r;

    $r = [];
    while ($row = $q->fetch(PDO::FETCH_NUM))
    {
      $r[] = (int) $row[0];
    }

    $q->closeCursor();
    return $r;
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

  public function isDeprecated()
  {
    return $this->getOption(self::OPTION_DEPRECATED);
  }

  public function isInResearch() {
    return $this->getOption(self::OPTION_RESEARCH);
  }

  public function isMarkdown() {
    return $this->getOption(self::OPTION_MARKDOWN);
  }

  public function isPublished() {
    return $this->getOption(self::OPTION_PUBLISHED);
  }

  public function jsonSerialize()
  {
    return [
      'created_datetime'            => $this->getCreatedDateTime(),
      'edited_count'                => $this->getEditedCount(),
      'edited_datetime'             => $this->getEditedDateTime(),
      'id'                          => $this->getId(),
      'options_bitmask'             => $this->getOptions(),
      'packet_application_layer_id' => $this->getApplicationLayerId(),
      'packet_direction_id'         => $this->getDirection(),
      'packet_format'               => $this->getFormat(),
      'packet_id'                   => $this->getPacketId(),
      'packet_name'                 => $this->getName(),
      'packet_remarks'              => $this->getRemarks(false),
      'packet_transport_layer_id'   => $this->getTransportLayerId(),
      'user'                        => $this->getUser(),
    ];
  }

  public function setApplicationLayerId(int $value)
  {
    if ($value < 0 || $value > self::MAX_APPLICATION_LAYER_ID)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_APPLICATION_LAYER_ID
      ));
    }

    $this->application_layer_id = $value;
  }

  public function setCreatedDateTime(DateTime $value)
  {
    $this->created_datetime = $value;
  }

  public function setDeprecated(bool $value)
  {
    $this->setOption(self::OPTION_DEPRECATED, $value);
  }

  public function setDirection(int $value)
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

  public function setEditedDateTime(?DateTime $value)
  {
    $this->edited_datetime = $value;
  }

  public function setFormat(string $value)
  {
    if (strlen($value) > self::MAX_FORMAT)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be less than or equal to %d characters', self::MAX_FORMAT
      ));
    }

    $this->format = $value;
  }

  public function setId(?int $value)
  {
    if (!is_null($value) && ($value < 0 || $value > self::MAX_ID))
    {
      throw new InvalidArgumentException(sprintf(
        'value must be between 0-%d', self::MAX_ID
      ));
    }

    $this->id = $value;
  }

  public function setInResearch(bool $value)
  {
    $this->setOption(self::OPTION_RESEARCH, $value);
  }

  public function setMarkdown(bool $value)
  {
    $this->setOption(self::OPTION_MARKDOWN, $value);
  }

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

  public function setName(string $value)
  {
    if (strlen($value) > self::MAX_NAME)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be less than or equal to %d characters', self::MAX_NAME
      ));
    }

    $this->name = $value;
  }

  /**
   * Sets the packet/message id. See also: setId()
   *
   * @param mixed $value The packet id. Supports decimal, hexadecimal, and octal format.
   * @throws InvalidArgumentException if value cannot be translated into an integer.
   * @throws OutOfBoundsException if value is not between zero and MAX_PACKET_ID.
   */
  public function setPacketId($value)
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
    else if (is_string($value) && strlen($value) >= 1 && substr($value, 0, 1) == '0')
    {
      // Octal (0123)
      $v = octdec(substr($value, 1));
    }
    else if (is_string($value) && strlen($value) >= 2 && (
      substr($value, 0, 2) == '&o' || substr($value, 0, 2) == '&O'))
    {
      // Octal (&o123, &O123)
      $v = octdec(substr($value, 1));
    }
    else if (is_numeric($value) && strpos($value, '.') === false)
    {
      // Decimal
      $v = (int) $value;
    }
    else
    {
      throw new InvalidArgumentException(
        'value must be a decimal, hexadecimal, or octal string or integer'
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

  public function setPublished(bool $value)
  {
    $this->setOption(self::OPTION_PUBLISHED, $value);
  }

  public function setRemarks(string $value)
  {
    if (strlen($value) > self::MAX_REMARKS)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be less than or equal to %d characters', self::MAX_REMARKS
      ));
    }

    $this->remarks = $value;
  }

  public function setTransportLayerId(int $value)
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
   * Sets the products this packet is used by.
   *
   * @param array $value The set of ProductId integers.
   */
  public function setUsedBy(array $value)
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try
    {
      Common::$database->beginTransaction();

      $q = Common::$database->prepare('DELETE FROM `packet_used_by` WHERE `id` = :id;');
      $q->bindParam(':id', $this->id, PDO::PARAM_INT);
      $r = $q->execute();
      if (!$r) return $r;

      $q = Common::$database->prepare('INSERT INTO `packet_used_by` (`id`, `bnet_product_id`) VALUES (?, ?);');
      foreach ($value as $v)
      {
        $r = $q->execute([(int) $this->id, (int) $v]);
        if (!$r)
        {
          Common::$database->rollBack();
          return $r;
        }
      }

      Common::$database->commit();
    }
    catch (PDOException $e)
    {
      Common::$database->rollBack();
      throw $e;
    }
  }

  public function setUser(?User $value)
  {
    $this->setUserId($value ? $value->getId() : $value);
  }

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
