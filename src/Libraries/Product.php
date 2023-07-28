<?php
namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Database;
use \StdClass;

class Product implements \BNETDocs\Interfaces\DatabaseObject, \JsonSerializable
{
  protected int $bnet_product_id;
  protected string $bnet_product_raw;
  protected int $bnls_product_id;
  protected string $label;
  protected int $sort;
  protected int $version_byte;

  public function __construct(StdClass|int|null $value)
  {
    if ($value instanceof StdClass)
    {
      $this->allocateObject($value);
    }
    else
    {
      $this->setBnetProductId($value);
      if (!$this->allocate()) throw new \BNETDocs\Exceptions\ProductNotFoundException($this);
    }
  }

  public function allocate(): bool
  {
    $this->setBnetProductRaw('');
    $this->setBnlsProductId(0);
    $this->setLabel('');
    $this->setSort(0);
    $this->setVersionByte(0);

    $id = $this->getBnetProductId();
    if (is_null($id)) return true;

    $q = Database::instance()->prepare('
      SELECT
        `bnet_product_id`,
        `bnet_product_raw`,
        `bnls_product_id`,
        `label`,
        `sort`,
        `version_byte`
      FROM `products`
      WHERE `bnet_product_id` = ?
      LIMIT 1;
    ');
    if (!$q || !$q->execute([$id]) || $q->rowCount() != 1) return false;
    $this->allocateObject($q->fetchObject());
    $q->closeCursor();
    return true;
  }

  protected function allocateObject(StdClass $value): void
  {
    $this->setBnetProductId($value->bnet_product_id);
    $this->setBnetProductRaw($value->bnet_product_raw);
    $this->setBnlsProductId($value->bnls_product_id);
    $this->setLabel($value->label);
    $this->setSort($value->sort);
    $this->setVersionByte($value->version_byte);
  }

  public function commit(): bool
  {
    return false;
  }

  /**
   * Deallocates the properties of this object from the database.
   *
   * @return boolean Whether the operation was successful.
   */
  public function deallocate(): bool
  {
    $id = $this->getBnetProductId();
    if (is_null($id)) return false;
    $q = Database::instance()->prepare('DELETE FROM `products` WHERE `bnet_product_id` = ? LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { if ($q) $q->closeCursor(); }
  }

  public static function getAllProducts(): ?array
  {
    $q = Database::instance()->prepare('
      SELECT
        `bnet_product_id`,
        `bnet_product_raw`,
        `bnls_product_id`,
        `label`,
        `sort`,
        `version_byte`
      FROM `products` ORDER BY `sort` ASC;
    ');
    if (!$q || !$q->execute()) return null;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  public static function getProductsFromIds(array $values): array
  {
    $r = [];
    foreach ($values as $value) $r[] = new self($value->bnet_product_id);
    return $r;
  }

  public function getBnetProductId(): ?int
  {
    return $this->bnet_product_id;
  }

  public function getBnetProductRaw(): string
  {
    return $this->bnet_product_raw;
  }

  public function getBnlsProductId(): int
  {
    return $this->bnls_product_id;
  }

  public function getLabel(): string
  {
    return $this->label;
  }

  public function getSort(): int
  {
    return $this->sort;
  }

  public function getVersionByte(): int
  {
    return $this->version_byte;
  }

  public function jsonSerialize(): mixed
  {
    return [
      'bnet_product_id' => $this->getBnetProductId(),
      'bnet_product_raw' => $this->getBnetProductRaw(),
      'bnls_product_id' => $this->getBnlsProductId(),
      'label' => $this->getLabel(),
      'sort' => $this->getSort(),
      'version_byte' => $this->getVersionByte(),
    ];
  }

  public function setBnetProductId(int $value): void
  {
    $this->bnet_product_id = $value;
  }

  public function setBnetProductRaw(string $value): void
  {
    $this->bnet_product_raw = $value;
  }

  public function setBnlsProductId(int $value): void
  {
    $this->bnls_product_id = $value;
  }

  public function setLabel(string $value): void
  {
    $this->label = $value;
  }

  public function setSort(int $value): void
  {
    $this->sort = $value;
  }

  public function setVersionByte(int $value): void
  {
    $this->version_byte = $value;
  }
}
