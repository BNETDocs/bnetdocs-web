<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Database;
use \OutOfBoundsException;
use \StdClass;

class ServerType implements \BNETDocs\Interfaces\DatabaseObject, \JsonSerializable
{
  public const MAX_ID = 0xFFFFFFFFFFFFFFFF; // bigint(20) unsigned
  public const MAX_LABEL = 0xFF; // varchar(255)

  protected ?int $id;
  protected string $label;

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

  public function allocate(): bool
  {
    $this->setLabel('');
    $id = $this->getId();
    if (is_null($id)) return true;
    $q = Database::instance()->prepare('SELECT `id`, `label` FROM `server_types` WHERE `id` = ? LIMIT 1;');
    if (!$q || !$q->execute([$id]) || $q->rowCount() != 1) return false;
    $this->allocateObject($q->fetchObject());
    $q->closeCursor();
    return true;
  }

  private function allocateObject(StdClass $value): void
  {
    $this->setId($value->id);
    $this->setLabel($value->label);
  }

  public function commit(): bool
  {
    $q = Database::instance()->prepare('UPDATE `server_types` SET `id` = :id, `label` = :label WHERE `id` = :id LIMIT 1;');
    $p = [':id' => $this->getId(), ':label' => $this->getLabel()];
    try { return $q && $q->execute($p) && $q->rowCount() === 1; }
    finally { if ($q) $q->closeCursor(); }
  }

  /**
   * Deallocates the properties of this object from the database.
   *
   * @return boolean Whether the operation was successful.
   */
  public function deallocate(): bool
  {
    $id = $this->getId();
    if (is_null($id)) return false;
    $q = Database::instance()->prepare('DELETE FROM `server_types` WHERE `id` = ? LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { $q->closeCursor(); }
  }

  public static function getAllServerTypes(): ?array
  {
    $q = Database::instance()->prepare('SELECT `id`, `label` FROM `server_types` ORDER BY `id` ASC;');
    if (!$q || !$q->execute()) return null;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getLabel(): string
  {
    return $this->label;
  }

  public function jsonSerialize(): mixed
  {
    return [
      'id' => $this->getId(),
      'label' => $this->getLabel(),
    ];
  }

  public function setId(?int $value): void
  {
    if ($value < 0 || $value > self::MAX_ID)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be null or an integer between 0-%d', self::MAX_ID
      ));
    }

    $this->id = $value;
  }

  public function setLabel(string $value): void
  {
    if (strlen($value) > self::MAX_LABEL)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be a string between 0-%d characters', self::MAX_LABEL
      ));
    }

    $this->label = $value;
  }
}
