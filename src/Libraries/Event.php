<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\User;
use \DateTimeInterface;
use \StdClass;

class Event implements \BNETDocs\Interfaces\DatabaseObject, \JsonSerializable
{
  protected DateTimeInterface $event_datetime;
  protected int $event_type_id;
  protected ?int $id;
  protected ?string $ip_address;
  protected mixed $meta_data;
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
      if (!$this->allocate()) throw new \UnexpectedValueException();
    }
  }

  public function allocate() : bool
  {
    $this->setEventDateTime('now');
    $this->setEventTypeId(\BNETDocs\Libraries\EventTypes::LOG_NOTE);
    $this->setIPAddress(null);
    $this->setMetadata(null);
    $this->setUserId(null);

    $id = $this->getId();
    if (is_null($id)) return true;

    $q = Database::instance()->prepare('
      SELECT
        `event_datetime`,
        `event_type_id`,
        `id`,
        `ip_address`,
        `meta_data`,
        `user_id`
      FROM `event_log` WHERE `id` = ? LIMIT 1;
    ');
    if (!$q || !$q->execute([$id]) || $q->rowCount() != 1) return false;
    $this->allocateObject($q->fetchObject());
    $q->closeCursor();
    return true;
  }

  protected function allocateObject(StdClass $value) : void
  {
    $this->setEventDateTime($value->event_datetime);
    $this->setEventTypeId($value->event_type_id);
    $this->setId($value->id);
    $this->setIPAddress($value->ip_address);
    $this->setMetadata(\json_decode($value->meta_data, true));
    $this->setUserId($value->user_id);
  }

  public function commit() : bool
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
        :edt, :etid, :id, :ip, :data, :uid
      ) ON DUPLICATE KEY UPDATE
        `event_datetime` = :edt,
        `event_type_id` = :etid,
        `id` = :id,
        `ip_address` = :ip,
        `meta_data` = :data,
        `user_id` = :uid;
    ');

    $p = [
      ':edt' => $this->getEventDateTime(),
      ':etid' => $this->getEventTypeId(),
      ':id' => $this->getId(),
      ':ip' => $this->getIPAddress(),
      ':data' => \json_encode($this->getMetadata(), (\JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR)),
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
    $q = Database::instance()->prepare('DELETE FROM `event_log` WHERE `id` = ? LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { $q->closeCursor(); }
  }

  public static function &getAllEvents($filter_types = null, ?array $order = null, ?int $limit = null, ?int $index = null) : ?array
  {
    if (empty($filter_types)) {
      $where_clause = '';
    } else {
      $where_clause = 'WHERE `event_type_id` IN ('
        . implode( ',', $filter_types ) . ')'
      ;
    }

    if (!(is_numeric($limit) || is_numeric($index))) {
      $limit_clause = '';
    } else if (!is_numeric($index)) {
      $limit_clause = 'LIMIT ' . (int) $limit;
    } else {
      $limit_clause = 'LIMIT ' . (int) $index . ',' . (int) $limit;
    }

    $q = Database::instance()->prepare(sprintf('
      SELECT
        `event_datetime`,
        `event_type_id`,
        `id`,
        `ip_address`,
        `meta_data`,
        `user_id`
      FROM `event_log` %s
      ORDER BY %s %s;
    ', $where_clause, (
      $order ? (sprintf('`%s` %s, `id` %s', $order[0], $order[1], $order[1])) : '`id` ASC'
    ), $limit_clause));
    if (!$q || !$q->execute()) return null;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  public static function getEventCount(?array $filter_types = null) : ?int
  {
    $where_clause = empty($filter_types) ? '' : sprintf(
      ' WHERE `event_type_id` IN (%s)', implode(',', $filter_types)
    );
    $q = Database::instance()->prepare(sprintf(
      'SELECT COUNT(*) AS `count` FROM `event_log`%s;', $where_clause
    ));
    if (!$q || !$q->execute() || $q->rowCount() != 1) return null;
    $r = $q->fetchObject()->count;
    $q->closeCursor();
    return $r;
  }

  public function getEventDateTime() : DateTimeInterface
  {
    return $this->event_datetime;
  }

  public function getEventTypeId() : int
  {
    return $this->event_type_id;
  }

  public function getEventTypeName() : string
  {
    return new \BNETDocs\Libraries\EventType($this->getEventTypeId());
  }

  public function getId() : ?int
  {
    return $this->id;
  }

  public function getIPAddress() : ?string
  {
    return $this->ip_address;
  }

  public function getMetadata() : mixed
  {
    return $this->meta_data;
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

  public function jsonSerialize(): mixed
  {
    return [
      'event_datetime' => $this->getEventDateTime(),
      'event_id' => $this->getId(),
      'event_ip_address' => $this->getIPAddress(),
      'event_meta_data' => $this->getMetadata(),
      'event_type_id' => $this->getEventTypeId(),
      'event_user' => $this->getUser(),
    ];
  }

  public function setEventDateTime(DateTimeInterface|string $value) : void
  {
    $this->event_datetime = (is_string($value) ?
      new \BNETDocs\Libraries\DateTimeImmutable($value, new \DateTimeZone(self::DATE_TZ)) : $value
    );
  }

  public function setEventTypeId(int $value) : void
  {
    $this->event_type_id = $value;
  }

  public function setId(?int $value) : void
  {
    $this->id = $value;
  }

  public function setIPAddress(?string $value) : void
  {
    $this->ip_address = $value;
  }

  public function setMetadata(mixed $value) : void
  {
    $this->meta_data = $value;
  }

  public function setUser(User $value) : void
  {
    $this->user_id = $value->getId();
  }

  public function setUserId(?int $value) : void
  {
    $this->user_id = $value;
  }
}
