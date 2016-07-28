<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\ProductNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;

class Product {

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
    $cache_key = "bnetdocs-products";
    $cache_val = Common::$cache->get($cache_key);
    if ($cache_val !== false && !empty($cache_val)) {
      $ids     = explode(",", $cache_val);
      $objects = [];
      foreach ($ids as $id) {
        $objects[] = new self($id);
      }
      return $objects;
    }
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
          `version_byte
        FROM `products`
        ORDER BY `sort` ASC;
      ");
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh products");
      }
      $ids     = [];
      $objects = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $ids[]     = (int) $row->bnet_product_id;
        $objects[] = new self($row);
        Common::$cache->set(
          "bnetdocs-product-" . $row->bnet_product_id, serialize($row), 300
        );
      }
      $stmt->closeCursor();
      Common::$cache->set($cache_key, implode(",", $ids), 300);
      return $objects;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh products", $e);
    }
    return null;
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
    $cache_key = "bnetdocs-product-" . $this->bnet_product_id;
    $cache_val = Common::$cache->get($cache_key);
    if ($cache_val !== false) {
      $cache_val = unserialize($cache_val);
      $this->bnet_product_id  = $cache_val->bnet_product_id;
      $this->bnet_product_raw = $cache_val->bnet_product_raw;
      $this->bnls_product_id  = $cache_val->bnls_product_id;
      $this->label            = $cache_val->label;
      $this->sort             = $cache_val->sort;
      $this->version_byte     = $cache_val->version_byte;
      return true;
    }
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
      Common::$cache->set($cache_key, serialize($row), 300);
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh product", $e);
    }
    return false;
  }

}
