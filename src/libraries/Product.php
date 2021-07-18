<?php
namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\ProductNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Database;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \InvalidArgumentException;
use \JsonSerializable;
use \PDO;
use \PDOException;
use \StdClass;

class Product implements JsonSerializable {

  protected $bnet_product_id;
  protected $bnet_product_raw;
  protected $bnls_product_id;
  protected $label;
  protected $sort;
  protected $version_byte;

  public function __construct($data) {
    if (is_numeric($data)) {
      $this->bnet_product_id  = (int) $data;
      $this->bnet_product_raw = null;
      $this->bnls_product_id  = null;
      $this->label            = null;
      $this->sort             = null;
      $this->version_byte     = null;
      $this->refresh();
    } else if ($data instanceof StdClass) {
      self::normalize($data);
      $this->bnet_product_id  = $data->bnet_product_id;
      $this->bnet_product_raw = $data->bnet_product_raw;
      $this->bnls_product_id  = $data->bnls_product_id;
      $this->label            = $data->label;
      $this->sort             = $data->sort;
      $this->version_byte     = $data->version_byte;
    } else {
      throw new InvalidArgumentException("Cannot use data argument");
    }
  }

  public static function getAllProducts() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `bnet_product_id`,
          `bnet_product_raw`,
          `bnls_product_id`,
          `label`,
          `sort`,
          `version_byte`
        FROM `products`
        ORDER BY `sort` ASC;
      ");
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh products");
      }
      $objects = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $objects[] = new self($row);
      }
      $stmt->closeCursor();
      return $objects;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh products", $e);
    }
    return null;
  }

  public static function getProductsFromIds($product_ids) {
    $products = [];
    if ($product_ids !== null) {
      foreach ($product_ids as $bnet_product_id) {
        $products[] = new self($bnet_product_id);
      }
    }
    return $products;
  }

  public function getBnetProductId() {
    return $this->bnet_product_id;
  }

  public function getBnetProductRaw() {
    return $this->bnet_product_raw;
  }

  public function getBnlsProductId() {
    return $this->bnls_product_id;
  }

  public function getLabel() {
    return $this->label;
  }

  public function getSort() {
    return $this->sort;
  }

  public function getVersionByte() {
    return $this->version_byte;
  }

  public function jsonSerialize()
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

  protected static function normalize(StdClass &$data) {
    $data->bnet_product_id  = (int)    $data->bnet_product_id;
    $data->bnet_product_raw = (string) $data->bnet_product_raw;
    $data->bnls_product_id  = (int)    $data->bnls_product_id;
    $data->label            = (string) $data->label;
    $data->sort             = (int)    $data->sort;
    $data->version_byte     = (int)    $data->version_byte;

    return true;
  }

  public function refresh() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `bnet_product_id`,
          `bnet_product_raw`,
          `bnls_product_id`,
          `label`,
          `sort`,
          `version_byte`
        FROM `products`
        WHERE `bnet_product_id` = :id
        LIMIT 1;
      ");
      $stmt->bindParam(":id", $this->bnet_product_id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh product");
      } else if ($stmt->rowCount() == 0) {
        throw new ProductNotFoundException($this->bnet_product_id);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      self::normalize($row);
      $this->bnet_product_id  = $row->bnet_product_id;
      $this->bnet_product_raw = $row->bnet_product_raw;
      $this->bnls_product_id  = $row->bnls_product_id;
      $this->label            = $row->label;
      $this->sort             = $row->sort;
      $this->version_byte     = $row->version_byte;
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh product", $e);
    }
    return false;
  }

}
