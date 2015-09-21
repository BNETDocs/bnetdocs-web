<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \PDO;
use \PDOException;

class User {

  private $created_datetime;
  private $display_name;
  private $email;
  private $id;
  private $options_bitmask;
  private $password_hash;
  private $password_salt;
  private $username;
  private $verified_datetime;

  public function __construct($user_id) {
    $this->created_datetime  = null;
    $this->display_name      = null;
    $this->email             = null;
    $this->id                = (int)$user_id;
    $this->options_bitmask   = null;
    $this->password_hash     = null;
    $this->password_salt     = null;
    $this->username          = null;
    $this->verified_datetime = null;
    $this->refresh();
  }

  public function checkPassword($password) {
    if (is_null($this->password_hash)
      || is_null($this->password_salt))
      return false;
    $pepper = Common::$config->bnetdocs->user_password_pepper;
    $salt   = $this->password_salt;
    $hash   = strtoupper(hash("sha256", $password.$salt.$pepper));
    return ($hash === strtoupper($this->password_hash));
  }

  public static function create(
    $email, $username, $display_name, $password, $options_bitmask
  ) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $password_hash = null; $password_salt = null;
    self::createPassword($password, $password_hash, $password_salt);
    $successful = false;
    try {
      $stmt = Common::$database->prepare("
        INSERT INTO `users` (
          `id`, `email`, `username`, `display_name`, `created_datetime`,
          `verified_datetime`, `password_hash`, `password_salt`,
          `options_bitmask`
        ) VALUES (
          NULL, :email, :username, :display_name, NOW(),
          NULL, :password_hash, :password_salt,
          :options_bitmask
        );
      ");
      $stmt->bindParam(":email", $email);
      $stmt->bindParam(":username", $username);
      $stmt->bindParam(":display_name", $display_name);
      $stmt->bindParam(":password_hash", $password_hash);
      $stmt->bindParam(":password_salt", $password_salt);
      $stmt->bindParam(":options_bitmask", $options_bitmask);
      $successful = $stmt->execute();
      $stmt->closeCursor();
    } catch (PDOException $e) {
      throw new QueryException("Cannot create user", $e);
    } finally {
      return $successful;
    }
  }

  public static function createPassword($password, &$hash, &$salt) {
    $pepper = Common::$config->bnetdocs->user_password_pepper;

    $gmp  = gmp_init(time());
    $gmp  = gmp_mul($gmp, mt_rand());
    $gmp  = gmp_mul($gmp, gmp_random_bits(64));
    $salt = strtoupper(gmp_strval($gmp, 36));

    $hash = strtoupper(hash("sha256", $password.$salt.$pepper));
  }

  public static function findIdByEmail($email) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT `id`
        FROM `users`
        WHERE `email` = :email
        LIMIT 1;
      ");
      $stmt->bindParam(":email", $email, PDO::PARAM_STR);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot query user id by email");
      } else if ($stmt->rowCount() == 0) {
        throw new UserNotFoundException($email);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      return (int)$row->id;
    } catch (PDOException $e) {
      throw new QueryException("Cannot query user id by email", $e);
    }
    return null;
  }

  public static function findIdByUsername($username) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT `id`
        FROM `users`
        WHERE `username` = :username
        LIMIT 1;
      ");
      $stmt->bindParam(":username", $username, PDO::PARAM_STR);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot query user id by username");
      } else if ($stmt->rowCount() == 0) {
        throw new UserNotFoundException($username);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      return (int)$row->id;
    } catch (PDOException $e) {
      throw new QueryException("Cannot query user id by username", $e);
    }
    return null;
  }

  public function getEmail() {
    return $this->email;
  }

  public function getId() {
    return $this->id;
  }

  public function getName() {
    return (is_null($this->display_name) ?
      $this->username : $this->display_name);
  }

  public static function getNameFromId($user_id) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT IFNULL(`display_name`, `username`) AS `name`
        FROM `users`
        WHERE `id` = :user_id
        LIMIT 1;
      ");
      $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot query name by user id");
      } else if ($stmt->rowCount() == 0) {
        throw new UserNotFoundException($user_id);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      return $row->name;
    } catch (PDOException $e) {
      throw new QueryException("Cannot query name by user id", $e);
    }
    return null;
  }

  public function refresh() {
    $cache_key = "bnetdocs-user-" . $this->id;
    $cache_val = Common::$cache->get($cache_key);
    if ($cache_val !== false) {
      $cache_val = unserialize($cache_val);
      $this->created_datetime  = $cache_val->created_datetime;
      $this->display_name      = $cache_val->display_name;
      $this->email             = $cache_val->email;
      $this->options_bitmask   = $cache_val->options_bitmask;
      $this->password_hash     = $cache_val->password_hash;
      $this->password_salt     = $cache_val->password_salt;
      $this->username          = $cache_val->username;
      $this->verified_datetime = $cache_val->verified_datetime;
      return true;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `created_datetime`,
          `display_name`,
          `email`,
          `options_bitmask`,
          `password_hash`,
          `password_salt`,
          `username`,
          `verified_datetime`
        FROM `users`
        WHERE `id` = :id
        LIMIT 1;
      ");
      $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh user");
      } else if ($stmt->rowCount() == 0) {
        throw new UserNotFoundException($this->id);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      $this->created_datetime  = $row->created_datetime;
      $this->display_name      = $row->display_name;
      $this->email             = $row->email;
      $this->options_bitmask   = $row->options_bitmask;
      $this->password_hash     = $row->password_hash;
      $this->password_salt     = $row->password_salt;
      $this->username          = $row->username;
      $this->verified_datetime = $row->verified_datetime;
      Common::$cache->set($cache_key, serialize($row), 300);
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh user", $e);
    }
    return false;
  }

}
