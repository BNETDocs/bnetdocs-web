<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DateTimeImmutable;
use \BNETDocs\Libraries\ServerType;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \DateTimeInterface;
use \DateTimeZone;
use \OutOfBoundsException;
use \StdClass;
use \UnexpectedValueException;

class Server implements \BNETDocs\Interfaces\DatabaseObject, \JsonSerializable
{
  public const MAX_ADDRESS = 0xFF; // varchar(255)
  public const MAX_ID = 0xFFFFFFFFFFFFFFFF; // bigint(20) unsigned
  public const MAX_LABEL = 0xFF; // varchar(255)
  public const MAX_PORT = 0xFFFF; // smallint(5) unsigned
  public const MAX_STATUS_BITMASK = 0xFF; // tinyint(3) unsigned
  public const MAX_TYPE_ID = ServerType::MAX_ID;
  public const MAX_USER_ID = User::MAX_ID;

  public const STATUS_ONLINE = 0x01;
  public const STATUS_DISABLED = 0x02;

  protected string $address;
  protected DateTimeInterface $created_datetime;
  protected ?int $id;
  protected ?string $label;
  protected int $port;
  protected int $status_bitmask;
  protected int $type_id;
  protected ?DateTimeInterface $updated_datetime;
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
      if (!$this->allocate()) throw new \BNETDocs\Exceptions\ServerNotFoundException($this);
    }
  }

  public function allocate() : bool
  {
    $this->setAddress('', true);
    $this->setCreatedDateTime('now');
    $this->setLabel(null);
    $this->setPort(0);
    $this->setStatusBitmask(self::STATUS_DISABLED);
    $this->setTypeId(0);
    $this->setUpdatedDateTime(null);
    $this->setUser(null);

    $id = $this->getId();
    if (is_null($id)) return true;

    $q = Database::instance()->prepare('
      SELECT
        `address`,
        `created_datetime`,
        `id`,
        `label`,
        `port`,
        `status_bitmask`,
        `type_id`,
        `updated_datetime`,
        `user_id`
      FROM `servers` WHERE `id` = ? LIMIT 1;
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
    $this->setAddress($value->address);
    $this->setCreatedDateTime($value->created_datetime);
    $this->setId($value->id);
    $this->setLabel($value->label);
    $this->setPort($value->port);
    $this->setStatusBitmask($value->status_bitmask);
    $this->setTypeId($value->type_id);
    $this->setUpdatedDateTime($value->updated_datetime);
    $this->setUserId($value->user_id);
  }

  public function commit() : bool
  {
    $q = Database::instance()->prepare('
      INSERT INTO `servers` (
        `address`,
        `created_datetime`,
        `id`,
        `label`,
        `port`,
        `status_bitmask`,
        `type_id`,
        `updated_datetime`,
        `user_id`
      ) VALUES (
        :a, :cdt, :id, :l, :p, :s, :tid, :udt, :uid
      ) ON DUPLICATE KEY UPDATE
        `address` = :a,
        `created_datetime` = :cdt,
        `label` = :l,
        `port` = :p,
        `status_bitmask` = :s,
        `type_id` = :tid,
        `updated_datetime` = :udt,
        `user_id` = :uid;
    ');

    $p = [
      ':a' => $this->getAddress(),
      ':cdt' => $this->getCreatedDateTime(),
      ':l' => $this->getLabel(),
      ':id' => $this->getId(),
      ':p' => $this->getPort(),
      ':s' => $this->getStatusBitmask(),
      ':tid' => $this->getTypeId(),
      ':udt' => $this->getUpdatedDateTime(),
      ':uid' => $this->getUserId(),
    ];

    foreach ($p as $k => &$v)
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
    $q = Database::instance()->prepare('DELETE FROM `servers` WHERE `id` = ? LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { $q->closeCursor(); }
  }

  public function getAddress() : string
  {
    return $this->address;
  }

  public static function getAllServers() : ?array
  {
    $q = Database::instance()->prepare('
      SELECT
        `address`,
        `created_datetime`,
        `id`,
        `label`,
        `port`,
        `status_bitmask`,
        `type_id`,
        `updated_datetime`,
        `user_id`
      FROM `servers`
      ORDER BY
        `type_id` ASC,
        ISNULL(`label`) ASC,
        `label` ASC,
        `address` ASC,
        `id` ASC;
    ');

    if (!$q || !$q->execute()) return null;

    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  public function getCreatedDateTime() : DateTimeInterface
  {
    return $this->created_datetime;
  }

  public function getName() : string
  {
    return empty($this->label) ? $this->address . ':' . $this->port : $this->label;
  }

  public function getId() : ?int
  {
    return $this->id;
  }

  public function getLabel() : ?string
  {
    return $this->label;
  }

  public function getPort() : int
  {
    return $this->port;
  }

  public static function getServersByUserId(int $user_id) : ?array
  {
    $q = Database::instance()->prepare('
      SELECT
        `address`,
        `created_datetime`,
        `id`,
        `label`,
        `port`,
        `status_bitmask`,
        `type_id`,
        `updated_datetime`,
        `user_id`
      FROM `servers`
      WHERE `user_id` = :user_id
      ORDER BY `id` ASC;
    ');

    if (!$q || !$q->execute([':user_id' => $user_id])) return null;

    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  public function getStatusBitmask() : int
  {
    return $this->status_bitmask;
  }

  public function getType() : ServerType
  {
    return new ServerType($this->type_id);
  }

  public function getTypeId() : int
  {
    return $this->type_id;
  }

  public function getURI() : string
  {
    $value = empty($value) ? $this->getAddress() . ':' . $this->getPort() : $this->getLabel();
    return Common::relativeUrlToAbsolute(sprintf(
      '/server/%d/%s', $this->getId(), Common::sanitizeForUrl($value, true)
    ));
  }

  public function getUpdatedDateTime() : ?DateTimeInterface
  {
    return $this->updated_datetime;
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

  public function isDisabled() : bool
  {
    return ($this->status_bitmask & self::STATUS_DISABLED);
  }

  public function isEnabled() : bool
  {
    return !$this->isDisabled();
  }

  public function isOffline() : bool
  {
    return !$this->isOnline();
  }

  public function isOnline() : bool
  {
    return ($this->status_bitmask & self::STATUS_ONLINE);
  }

  public function jsonSerialize() : mixed
  {
    return [
      'address' => $this->getAddress(),
      'created_datetime' => $this->getCreatedDateTime(),
      'id' => $this->getId(),
      'label' => $this->getLabel(),
      'port' => $this->getPort(),
      'status_bitmask' => $this->getStatusBitmask(),
      'type_id' => $this->getTypeId(),
      'updated_datetime' => $this->getUpdatedDateTime(),
      'uri' => $this->getURI(),
      'user_id' => $this->getUserId(),
    ];
  }

  public function setAddress(string $value, bool $allow_empty = false) : void
  {
    if ((!$allow_empty && empty($value)) || strlen($value) > self::MAX_ADDRESS)
        throw new OutOfBoundsException(sprintf(
          'value must be a string between 1-%d', self::MAX_ADDRESS
        ));

    $this->address = $value;
  }

  public function setCreatedDateTime(DateTimeInterface|string $value) : void
  {
    $this->created_datetime = (is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value
    );
  }

  public function setDisabled(bool $value = true) : void
  {
    if ($value) $this->status_bitmask |= self::STATUS_DISABLED;
    else $this->status_bitmask &= ~self::STATUS_DISABLED;
  }

  public function setEnabled(bool $value = true) : void
  {
    $this->setDisabled(!$value);
  }

  public function setId(?int $value) : void
  {
    if ($value < 0 || $value > self::MAX_ID)
      throw new OutOfBoundsException(sprintf(
        'value must be null or an integer between 0-%d', self::MAX_ID
      ));

    $this->id = $value;
  }

  public function setLabel(?string $value, bool $auto_null = true) : void
  {
    if (!is_null($value) && strlen($value) > self::MAX_LABEL)
      throw new OutOfBoundsException(sprintf(
        'value must be a string between 0-%d characters', self::MAX_LABEL
      ));

    $this->label = empty($value) && $auto_null ? null : $value;
  }

  public function setOffline(bool $value = true) : void
  {
    $this->setOnline(!$value);
  }

  public function setOnline(bool $value = true) : void
  {
    if ($value) $this->status_bitmask |= self::STATUS_ONLINE;
    else $this->status_bitmask &= ~self::STATUS_ONLINE;
  }

  public function setPort(int $value) : void
  {
    if ($value < 0 || $value > self::MAX_PORT)
      throw new OutOfBoundsException(sprintf(
        'value must be null or an integer between 0-%d', self::MAX_PORT
      ));

    $this->port = $value;
  }

  public function setStatusBitmask(int $value) : void
  {
    if ($value < 0 || $value > self::MAX_STATUS_BITMASK)
      throw new OutOfBoundsException(sprintf(
        'value must be null or an integer between 0-%d', self::MAX_STATUS_BITMASK
      ));

    $this->status_bitmask = $value;
  }

  public function setType(ServerType $value) : void
  {
    $this->setTypeId($value instanceof ServerType ? $value->getId() : $value);
  }

  public function setTypeId(int $value) : void
  {
    if ($value < 0 || $value > self::MAX_TYPE_ID)
      throw new OutOfBoundsException(sprintf(
        'value must be null or an integer between 0-%d', self::MAX_TYPE_ID
      ));

    $this->type_id = $value;
  }

  public function setUpdatedDateTime(DateTimeInterface|string|null $value) : void
  {
    $this->updated_datetime = (is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value
    );
  }

  public function setUser(?User $value) : void
  {
    $this->setUserId($value instanceof User ? $value->getId() : $value);
  }

  public function setUserId(?int $value) : void
  {
    if ($value < 0 || $value > self::MAX_USER_ID)
      throw new OutOfBoundsException(sprintf(
        'value must be null or an integer between 0-%d', self::MAX_USER_ID
      ));

    $this->user_id = $value;
  }
}
