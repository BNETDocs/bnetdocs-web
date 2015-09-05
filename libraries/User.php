<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \PDO;
use \PDOException;

class User {

  public static function create(
    $email, $username, $display_name, $password_hash, $password_salt,
    $status_bitmask
  ) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $successful = false;
    try {
      $stmt = Common::$database->prepare("
        INSERT INTO `users` (
          `id`, `email`, `username`, `display_name`, `password_hash`,
          `password_salt`, `status_bitmask`, `registered_date`,
          `verified_date`, `verified_id`
        ) VALUES (
          NULL, :email, :username, :display_name, :password_hash,
          :password_salt, :status_bitmask, NOW(), NULL, NULL
        );
      ");
      $stmt->bindParam(":email", $email);
      $stmt->bindParam(":username", $username);
      $stmt->bindParam(":display_name", $display_name);
      $stmt->bindParam(":password_hash", $password_hash);
      $stmt->bindParam(":password_salt", $password_salt);
      $stmt->bindParam(":status_bitmask", $status_bitmask);
      $successful = $stmt->execute();
      $stmt->closeCursor();
    } catch (PDOException $e) {
      throw new QueryException("Cannot create user", $e);
    } finally {
      return $successful;
    }
  }

  public static function findIdByEmail($email) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $user_id = null;
    try {
      $stmt = Common::$database->prepare("
        SELECT `id`
        FROM `users`
        WHERE `email` = :email
        LIMIT 1;
      ");
      $stmt->bindParam(":email", $email, PDO::PARAM_STR);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      $user_id = (int)$row->id;
      // What if the email simply doesn't exist? throw QueryException?
    } catch (PDOException $e) {
      throw new QueryException("Cannot query user id by email", $e);
    } finally {
      return $user_id;
    }
  }

  public static function findIdByUsername($username) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $user_id = null;
    try {
      $stmt = Common::$database->prepare("
        SELECT `id`
        FROM `users`
        WHERE `username` = :username
        LIMIT 1;
      ");
      $stmt->bindParam(":username", $username, PDO::PARAM_STR);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      $user_id = (int)$row->id;
      // What if the username simply doesn't exist? throw QueryException?
    } catch (PDOException $e) {
      throw new QueryException("Cannot query user id by username", $e);
    } finally {
      return $user_id;
    }
  }

  public static function getName($user_id) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $name = null;
    try {
      $stmt = Common::$database->prepare("
        SELECT IFNULL(`display_name`, `username`) AS `name`
        FROM `users`
        WHERE `id` = :user_id
        LIMIT 1;
      ");
      $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      $name = $row->name;
    } catch (PDOException $e) {
      throw new QueryException("Cannot query name by user id", $e);
    } finally {
      return $user_id;
    }
  }

}
