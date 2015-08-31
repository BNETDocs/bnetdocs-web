<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \PDO;
use \PDOException;

class User {

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
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $user_id = (int)$row->id;
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
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $user_id = (int)$row->id;
    } catch (PDOException $e) {
      throw new QueryException("Cannot query user id by username", $e);
    } finally {
      return $user_id;
    }
  }

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
    } catch (PDOException $e) {
      throw new QueryException("Cannot create user", $e);
    } finally {
      return $successful;
    }
  }

}
