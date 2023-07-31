<?php

namespace BNETDocs\Libraries\EventLog;

use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DateTimeImmutable;
use \BNETDocs\Libraries\EventLog\EventTypes;
use \BNETDocs\Libraries\User;
use \DateTimeInterface;
use \DateTimeZone;
use \LengthException;
use \OutOfBoundsException;
use \StdClass;

class Event implements \BNETDocs\Interfaces\DatabaseObject, \JsonSerializable
{
  public const MAX_ID = 0xFFFFFFFFFFFFFFFF;
  public const MAX_IP_ADDRESS = 0xFF;
  public const MAX_META_DATA = 0xFFFFFF;
  public const MAX_TYPE_ID = 0xFFFFFFFFFFFFFFFF;
  public const MAX_USER_ID = 0xFFFFFFFFFFFFFFFF;

  private ?DateTimeInterface $datetime = null;
  private ?int $id = null;
  private ?string $ip_address = null;
  private mixed $meta_data = null;
  private int $type_id = 0;
  private ?int $user_id = null;

  public function __construct(StdClass|int|null $value = null)
  {
    if ($value instanceof StdClass)
    {
      $this->allocateObject($value);
    }
    else
    {
      $this->setId($value);
      if (!$this->allocate())
      {
        throw new \BNETDocs\Exceptions\EventNotFoundException($value);
      }
    }
  }

  public function allocate(): bool
  {
    $this->setDateTime(null);
    $this->setIPAddress(null);
    $this->setMetaData(null);
    $this->setTypeId(EventTypes::LOG_NOTE);
    $this->setUserId(null);

    $id = $this->getId();
    if (\is_null($id)) return true;

    try
    {
      $q = Database::instance()->prepare('
        SELECT
          `event_datetime` AS `datetime`,
          `event_type_id` AS `type_id`,
          `id`,
          `ip_address`,
          `meta_data`,
          `user_id`
        FROM `event_log` WHERE `id` = ? LIMIT 1;
      ');
      if (!$q || !$q->execute([$id]) || $q->rowCount() != 1) return false;
      $this->allocateObject($q->fetchObject());
    }
    finally
    {
      if ($q) $q->closeCursor();
    }

    return true;
  }

  public static function allocateAll(mixed $filter = null, string $order_column = '', bool $descending = false, ?int $limit = null, ?int $offset = null): ?array
  {
    try
    {
      $where_clause = empty($filter) ? '' : \sprintf(
        ' WHERE `event_type_id` IN (%s)', \implode(',', $filter)
      );
      $sort = $descending ? 'DESC' : 'ASC';
      $order_clause = ' ORDER BY ' . (empty($order_column) ? \sprintf('`id` %s', $sort)
        : \sprintf('`%s` %s, `id` %s', $order_column, $sort, $sort));
      $limit_clause = ' LIMIT ' . $offset . ',' . $limit;
      $q = \sprintf('
        SELECT
          `event_datetime` AS `datetime`,
          `event_type_id` AS `type_id`,
          `id`,
          `ip_address`,
          `meta_data`,
          `user_id`
        FROM `event_log`%s%s%s;', $where_clause, $order_clause, $limit_clause
      );
      $q = Database::instance()->prepare($q);
      if (!$q || !$q->execute()) return null;
      $r = [];
      while ($row = $q->fetchObject()) $r[] = new self($row);
      return $r;
    }
    finally
    {
      if ($q) $q->closeCursor();
    }
  }

  public function allocateObject(StdClass $value): void
  {
    $this->setDateTime($value->datetime);
    $this->setId($value->id);
    $this->setIPAddress($value->ip_address);
    $this->setMetaData(\json_decode($value->meta_data, true, 512, \JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR));
    $this->setTypeId($value->type_id);
    $this->setUserId($value->user_id);
  }

  public function commit(): bool
  {
    try
    {
      $q = Database::instance()->prepare('
        INSERT INTO `event_log` (
          `event_datetime`,
          `event_type_id`,
          `id`,
          `ip_address`,
          `meta_data`,
          `user_id`
        ) VALUES (
          :dt, :tid, :id, :ip, :md, :uid
        ) ON DUPLICATE KEY UPDATE
          `event_datetime` = :dt,
          `event_type_id` = :tid,
          `id` = :id,
          `ip_address` = :ip,
          `meta_data` = :md,
          `user_id` = :uid;
      ');

      $p = [
        ':dt' => $this->getDateTime(),
        ':id' => $this->getId(),
        ':ip' => $this->getIPAddress(),
        ':md' => \json_encode($this->getMetaData(), \JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR),
        ':tid' => $this->getTypeId(),
        ':uid' => $this->getUserId(),
      ];

      foreach ($p as $k => $v)
      {
        if ($v instanceof DateTimeInterface)
        {
          $p[$k] = $v->format(self::DATE_SQL);
        }
      }

      if (!$q || !$q->execute($p)) return false;
      if (\is_null($p[':id'])) $this->setId(Database::instance()->lastInsertId());
    }
    finally
    {
      if ($q) $q->closeCursor();
    }

    return true;
  }

  public function deallocate(): bool
  {
    $id = $this->getId();
    if (\is_null($id)) return false;
    $q = Database::instance()->prepare('DELETE FROM `event_log` WHERE `id` = ? LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { if ($q) $q->closeCursor(); }
  }

  public static function countAll(mixed $filter = null): ?int
  {
    try
    {
      $where_clause = empty($filter) ? '' : \sprintf(
        ' WHERE `event_type_id` IN (%s)', \implode(',', $filter)
      );
      $q = Database::instance()->prepare(sprintf(
        'SELECT COUNT(*) AS `count` FROM `event_log`%s;', $where_clause
      ));
      if (!$q || !$q->execute() || $q->rowCount() != 1) return null;
      return $q->fetchObject()->count;
    }
    finally
    {
      if ($q) $q->closeCursor();
    }
  }

  public function getDateTime(): ?DateTimeInterface
  {
    return $this->datetime;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getIPAddress(): ?string
  {
    return $this->ip_address;
  }

  public function getMetaData(): mixed
  {
    return $this->meta_data;
  }

  public function getTypeId(): int
  {
    return $this->type_id;
  }

  public function getTypeName(): string
  {
    $type = new \BNETDocs\Libraries\EventLog\EventType($this->getTypeId());
    return (string) $type;
  }

  public function getUser(): ?User
  {
    return \is_null($this->user_id) ? null : new User($this->user_id);
  }

  public function getUserId(): ?int
  {
    return $this->user_id;
  }

  public function jsonSerialize(): mixed
  {
    return [
      'datetime' => $this->getDateTime(),
      'id' => $this->getId(),
      'ip_address' => $this->getIPAddress(),
      'meta_data' => $this->getMetaData(),
      'type_id' => $this->getTypeId(),
      'user_id' => $this->getUserId(),
    ];
  }

  public function setDateTime(DateTimeInterface|string|null $value): void
  {
    $this->datetime = \is_null($value) ? null : (
      \is_string($value) ? new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : DateTimeImmutable::createFromInterface($value)
    );
  }

  public function setId(?int $value): void
  {
    if (!\is_null($value) && ($value < 0 || $value > self::MAX_ID))
    {
      throw new OutOfBoundsException(\sprintf('value must be null or an integer between 0-%d, got: %d', self::MAX_ID, $value));
    }

    $this->id = $value;
  }

  public function setIPAddress(?string $value): void
  {
    if (\is_string($value))
    {
      $l = \strlen($value);
      if ($l < 1 || $l > self::MAX_IP_ADDRESS || !\filter_var($value, \FILTER_VALIDATE_IP))
      {
        throw new LengthException(\sprintf('value must be null or a formatted IP address string between 1-%d characters, got: %d characters', self::MAX_IP_ADDRESS, $l));
      }
    }

    $this->ip_address = $value;
  }

  public function setMetaData(mixed $value): void
  {
    $v = \is_null($value) ? null : \json_encode($value, \JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR);
    $l = \is_null($v) ? 0 : \strlen($v);

    if ($l > self::MAX_META_DATA)
    {
      throw new LengthException(\sprintf('value must be null or a string between 0-%d characters, got: %d characters', self::MAX_META_DATA, $l));
    }

    $this->meta_data = $value;
  }

  public function setTypeId(int $value): void
  {
    if ($value < 0 || $value > self::MAX_TYPE_ID)
    {
      throw new OutOfBoundsException(\sprintf('value must be an integer between 0-%d, got: %d', self::MAX_TYPE_ID, $value));
    }

    $this->type_id = $value;
  }

  public function setUserId(User|int|null $value): void
  {
    $v = $value instanceof User ? $value->getId() : $value;

    if (!\is_null($v) && ($v < 0 || $v > self::MAX_USER_ID))
    {
      throw new OutOfBoundsException(\sprintf('value must be null or an integer between 0-%d, got: %d', self::MAX_TYPE_ID, $v));
    }

    $this->user_id = $v;
  }
}
