<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;

class User {

  const OPTION_ACL_DOCUMENT_CREATE  = 1;
  const OPTION_ACL_DOCUMENT_MODIFY  = 2;
  const OPTION_ACL_DOCUMENT_DELETE  = 4;
  const OPTION_ACL_EVENT_LOG_VIEW   = 8;
  const OPTION_ACL_EVENT_LOG_MODIFY = 16;
  const OPTION_ACL_EVENT_LOG_DELETE = 32;
  const OPTION_ACL_NEWS_CREATE      = 64;
  const OPTION_ACL_NEWS_MODIFY      = 128;
  const OPTION_ACL_NEWS_DELETE      = 256;
  const OPTION_ACL_PACKET_CREATE    = 512;
  const OPTION_ACL_PACKET_MODIFY    = 1024;
  const OPTION_ACL_PACKET_DELETE    = 2048;
  const OPTION_ACL_SERVER_CREATE    = 4096;
  const OPTION_ACL_SERVER_MODIFY    = 8192;
  const OPTION_ACL_SERVER_DELETE    = 16384;
  const OPTION_ACL_USER_CREATE      = 32768;
  const OPTION_ACL_USER_MODIFY      = 65536;
  const OPTION_ACL_USER_DELETE      = 131072;
  const OPTION_DISABLED             = 262144;
  const OPTION_VERIFIED             = 524288;

  protected $created_datetime;
  protected $display_name;
  protected $email;
  protected $id;
  protected $options_bitmask;
  protected $password_hash;
  protected $password_salt;
  protected $username;
  protected $verified_datetime;

  public function __construct($data) {
    if (is_numeric($data)) {
      $this->created_datetime  = null;
      $this->display_name      = null;
      $this->email             = null;
      $this->id                = (int) $data;
      $this->options_bitmask   = null;
      $this->password_hash     = null;
      $this->password_salt     = null;
      $this->username          = null;
      $this->verified_datetime = null;
      $this->refresh();
    } else if ($data instanceof StdClass) {
      self::normalize($data);
      $this->created_datetime  = $data->created_datetime;
      $this->display_name      = $data->display_name;
      $this->email             = $data->email;
      $this->id                = $data->id;
      $this->options_bitmask   = $data->options_bitmask;
      $this->password_hash     = $data->password_hash;
      $this->password_salt     = $data->password_salt;
      $this->username          = $data->username;
      $this->verified_datetime = $data->verified_datetime;
    } else {
      throw new InvalidArgumentException("Cannot use data argument");
    }
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
      return (int) $row->id;
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
      return (int) $row->id;
    } catch (PDOException $e) {
      throw new QueryException("Cannot query user id by username", $e);
    }
    return null;
  }

  public function getCreatedDateTime() {
    if (is_null($this->created_datetime)) {
      return $this->created_datetime;
    } else {
      $tz = new DateTimeZone("UTC");
      $dt = new DateTime($this->created_datetime);
      $dt->setTimezone($tz);
      return $dt;
    }
  }

  public function getDisplayName() {
    return $this->display_name;
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

  public function getOptionsBitmask() {
    return $this->options_bitmask;
  }

  public function getPasswordHash() {
    return $this->password_hash;
  }

  public function getPasswordSalt() {
    return $this->password_salt;
  }

  public function getUsername() {
    return $this->username;
  }

  public function getVerifiedDateTime() {
    if (is_null($this->verified_datetime)) {
      return $this->verified_datetime;
    } else {
      $tz = new DateTimeZone("UTC");
      $dt = new DateTime($this->verified_datetime);
      $dt->setTimezone($tz);
      return $dt;
    }
  }

  protected static function normalize(StdClass &$data) {
    $data->created_datetime = (string) $data->created_datetime;
    $data->email            = (string) $data->email;
    $data->options_bitmask  = (int)    $data->options_bitmask;
    $data->username         = (string) $data->username;

    if (!is_null($data->display_name))
      $data->display_name = (string) $data->display_name;

    if (!is_null($data->password_hash))
      $data->password_hash = (string) $data->password_hash;

    if (!is_null($data->password_salt))
      $data->password_salt = (string) $data->password_salt;

    if (!is_null($data->verified_datetime))
      $data->verified_datetime = (string) $data->verified_datetime;

    return true;
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
          `id`,
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
      self::normalize($row);
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
