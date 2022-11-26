<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Database;
use \StdClass;

class NewsCategory implements \BNETDocs\Interfaces\DatabaseObject, \JsonSerializable
{
  public const MAX_FILENAME = 255;
  public const MAX_LABEL = 255;

  protected string $filename;
  protected ?int $id;
  protected string $label;
  protected int $sort_id;

  public function __construct(StdClass|int|null $value)
  {
    if ($value instanceof StdClass)
    {
      $this->allocateObject($value);
    }
    else
    {
      $this->setId($value);
      if (!$this->allocate()) throw new \BNETDocs\Exceptions\NewsCategoryNotFoundException($this);
    }
  }

  public function allocate() : bool
  {
    $this->setFilename('');
    $this->setLabel('');
    $this->setSortId(0);

    $id = $this->getId();
    if (is_null($id)) return true;

    $q = Database::instance()->prepare('
      SELECT
        `filename`,
        `id`,
        `label`,
        `sort_id`
      FROM `news_categories` WHERE `id` = ? LIMIT 1;
    ');
    if (!$q || !$q->execute([$this->getId()])) return false;
    $this->allocateObject($q->fetchObject());
    $q->closeCursor();
    return true;
  }

  protected function allocateObject(StdClass $value) : void
  {
    $this->setFilename($value->filename);
    $this->setId($value->id);
    $this->setLabel($value->label);
    $this->setSortId($value->sort_id);
  }

  public function commit() : bool
  {
    $q = Database::instance()->prepare('
      INSERT INTO `news_categories` (
        `filename`,
        `id`,
        `label`,
        `sort_id`
      ) VALUES (
        :f, :id, :l, :s
      ) ON DUPLICATE KEY UPDATE
        `filename` = :f,
        `id` = :id,
        `label` = :l,
        `sort_id` = :s;
    ');

    $p = [
      ':f' => $this->getFilename(),
      ':id' => $this->getId(),
      ':l' => $this->getLabel(),
      ':s' => $this->getSortId(),
    ];

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
    $q = Database::instance()->prepare('DELETE FROM `news_categories` WHERE `id` = ? LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { $q->closeCursor(); }
  }

  public static function getAll() : ?array
  {
    $q = Database::instance()->prepare('
      SELECT
        `filename`,
        `id`,
        `label`,
        `sort_id`
      FROM `news_categories` ORDER BY `id` ASC;
    ');
    if (!$q || !$q->execute()) return null;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  public function getFilename() : string
  {
    return $this->filename;
  }

  public function getId() : ?int
  {
    return $this->id;
  }

  public function getLabel() : string
  {
    return $this->label;
  }

  public function getSortId() : int
  {
    return $this->sort_id;
  }

  public function jsonSerialize() : mixed
  {
    return [
      'filename' => $this->getFilename(),
      'id' => $this->getId(),
      'label' => $this->getLabel(),
      'sort_id' => $this->getSortId(),
    ];
  }

  public function setFilename(string $value) : void
  {
    $this->filename = $value;
  }

  public function setId(?int $value) : void
  {
    $this->id = $value;
  }

  public function setLabel(string $value) : void
  {
    $this->label = $value;
  }

  public function setSortId(int $value) : void
  {
    $this->sort_id = $value;
  }
}
