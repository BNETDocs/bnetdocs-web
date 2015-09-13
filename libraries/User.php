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

  private $id;
  private $email;
  private $username;
  private $display_name;
  private $password_hash;
  private $password_salt;
  private $status_bitmask;
  private $registered_date;
  private $verified_date;
  private $verified_id;

  public function __construct($user_id) {
    $this->id              = (int)$user_id;
    $this->email           = null;
    $this->username        = null;
    $this->display_name    = null;
    $this->password_hash   = null;
    $this->password_salt   = null;
    $this->status_bitmask  = null;
    $this->registered_date = null;
    $this->verified_date   = null;
    $this->verified_id     = null;
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
    $email, $username, $display_name, $password, $status_bitmask
  ) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $password_hash = null; $password_salt = null;
    self::createPassword($password, $password_hash, $password_salt);
    $verified_id = mt_rand();
    $successful = false;
    try {
      $stmt = Common::$database->prepare("
        INSERT INTO `users` (
          `id`, `email`, `username`, `display_name`, `password_hash`,
          `password_salt`, `status_bitmask`, `registered_date`,
          `verified_date`, `verified_id`
        ) VALUES (
          NULL, :email, :username, :display_name, :password_hash,
          :password_salt, :status_bitmask, NOW(), NULL, :verified_id
        );
      ");
      $stmt->bindParam(":email", $email);
      $stmt->bindParam(":username", $username);
      $stmt->bindParam(":display_name", $display_name);
      $stmt->bindParam(":password_hash", $password_hash);
      $stmt->bindParam(":password_salt", $password_salt);
      $stmt->bindParam(":status_bitmask", $status_bitmask);
      $stmt->bindParam(":verified_id", $verified_id);
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
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `email`,
          `username`,
          `display_name`,
          `password_hash`,
          `password_salt`,
          `status_bitmask`,
          `registered_date`,
          `verified_date`,
          `verified_id`
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
      $this->email           = $row->email;
      $this->username        = $row->username;
      $this->display_name    = $row->display_name;
      $this->password_hash   = $row->password_hash;
      $this->password_salt   = $row->password_salt;
      $this->status_bitmask  = $row->status_bitmask;
      $this->registered_date = $row->registered_date;
      $this->verified_date   = $row->verified_date;
      $this->verified_id     = $row->verified_id;
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh user", $e);
    }
    return false;
  }

}
