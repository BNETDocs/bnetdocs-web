<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Credits;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\Exceptions\UserProfileNotFoundException;
use \BNETDocs\Libraries\UserProfile;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Database;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Gravatar;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \JsonSerializable;
use \PDO;
use \PDOException;
use \StdClass;

class User implements JsonSerializable {

  const DEFAULT_OPTION              = 0x00000020;

  const OPTION_DISABLED             = 0x00000001;
  const OPTION_VERIFIED             = 0x00000002;
  const OPTION_ACL_DOCUMENT_CREATE  = 0x00000004;
  const OPTION_ACL_DOCUMENT_MODIFY  = 0x00000008;
  const OPTION_ACL_DOCUMENT_DELETE  = 0x00000010;
  const OPTION_ACL_COMMENT_CREATE   = 0x00000020;
  const OPTION_ACL_COMMENT_MODIFY   = 0x00000040;
  const OPTION_ACL_COMMENT_DELETE   = 0x00000080;
  const OPTION_ACL_EVENT_LOG_VIEW   = 0x00000100;
  const OPTION_ACL_EVENT_LOG_MODIFY = 0x00000200;
  const OPTION_ACL_EVENT_LOG_DELETE = 0x00000400;
  const OPTION_ACL_NEWS_CREATE      = 0x00000800;
  const OPTION_ACL_NEWS_MODIFY      = 0x00001000;
  const OPTION_ACL_NEWS_DELETE      = 0x00002000;
  const OPTION_ACL_PACKET_CREATE    = 0x00004000;
  const OPTION_ACL_PACKET_MODIFY    = 0x00008000;
  const OPTION_ACL_PACKET_DELETE    = 0x00010000;
  const OPTION_ACL_SERVER_CREATE    = 0x00020000;
  const OPTION_ACL_SERVER_MODIFY    = 0x00040000;
  const OPTION_ACL_SERVER_DELETE    = 0x00080000;
  const OPTION_ACL_USER_CREATE      = 0x00100000;
  const OPTION_ACL_USER_MODIFY      = 0x00200000;
  const OPTION_ACL_USER_DELETE      = 0x00400000;

  protected $created_datetime;
  protected $display_name;
  protected $email;
  protected $id;
  protected $options_bitmask;
  protected $password_hash;
  protected $password_salt;
  protected $timezone;
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
      $this->timezone          = null;
      $this->username          = null;
      $this->verified_datetime = null;
      $this->verifier_token    = null;
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
      $this->timezone          = $data->timezone;
      $this->username          = $data->username;
      $this->verified_datetime = $data->verified_datetime;
      $this->verifier_token    = $data->verifier_token;
    } else {
      throw new InvalidArgumentException("Cannot use data argument");
    }
  }

  public function changeDisplayName($new_display_name) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $successful = false;
    try {
      $stmt = Common::$database->prepare('
        UPDATE `users` SET `display_name` = :dn WHERE `id` = :user_id;
      ');
      $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
      if (is_null($new_display_name)) {
        $stmt->bindParam(':dn', $new_display_name, PDO::PARAM_NULL);
      } else {
        $stmt->bindParam(':dn', $new_display_name, PDO::PARAM_STR);
      }
      $successful = $stmt->execute();
      $stmt->closeCursor();
      if ($successful) {
        if (is_null($new_display_name)) {
          $this->display_name = $new_display_name;
        } else {
          $this->display_name = (string) $new_display_name;
        }
      }
    } catch (PDOException $e) {
      throw new QueryException('Cannot change user display name', $e);
    } finally {
      return $successful;
    }
  }

  public function changeEmail($new_email) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $successful = false;
    try {
      $stmt = Common::$database->prepare('
        UPDATE `users` SET `email` = :email WHERE `id` = :user_id;
      ');
      $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
      $stmt->bindParam(':email', $new_email, PDO::PARAM_STR);
      $successful = $stmt->execute();
      $stmt->closeCursor();
      if ($successful) {
        $this->email = (string) $new_email;
      }
    } catch (PDOException $e) {
      throw new QueryException('Cannot change user email', $e);
    } finally {
      return $successful;
    }
  }

  public function changePassword($new_password) {
    $password_hash = self::createPassword($new_password);
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $successful = false;
    try {
      $stmt = Common::$database->prepare('
        UPDATE `users` SET
          `password_hash` = :password_hash, `password_salt` = NULL
        WHERE `id` = :user_id;
      ');
      $stmt->bindParam(":user_id", $this->id, PDO::PARAM_INT);
      $stmt->bindParam(":password_hash", $password_hash, PDO::PARAM_STR);
      $successful = $stmt->execute();
      $stmt->closeCursor();
      if ($successful) {
        $this->password_hash = (string) $password_hash;
      }
    } catch (PDOException $e) {
      throw new QueryException("Cannot change user password", $e);
    } finally {
      return $successful;
    }
  }

  public function changeUsername($new_username) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $successful = false;
    try {
      $stmt = Common::$database->prepare('
        UPDATE `users` SET `username` = :username WHERE `id` = :user_id;
      ');
      $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
      $stmt->bindParam(':username', $new_username, PDO::PARAM_STR);
      $successful = $stmt->execute();
      $stmt->closeCursor();
      if ($successful) {
        $this->username = (string) $new_username;
      }
    } catch (PDOException $e) {
      throw new QueryException('Cannot change username of user', $e);
    } finally {
      return $successful;
    }
  }

  public function checkPassword($password) {
    if (is_null($this->password_hash)) {
      // no password set
      return false;
    }

    if (substr($this->password_hash, 0, 1) == '$') {
      // new style bcrypt password

      $cost = Common::$config->bnetdocs->user_password_bcrypt_cost;
      $match = password_verify($password, $this->password_hash);
      $rehash = password_needs_rehash(
        $this->password_hash, PASSWORD_BCRYPT, array('cost' => $cost)
      );

      return ($match && !$rehash); // will deny if not match or needs rehash

    } else {
      // old style sha256 password

      $pepper = Common::$config->bnetdocs->user_password_pepper;
      $salt = $this->password_salt;
      $hash = strtoupper(hash('sha256', $password.$salt.$pepper));

      return ($hash === strtoupper($this->password_hash));
    }
  }

  public static function create(
    $email, $username, $display_name, $password, $options_bitmask
  ) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $verifier_token = self::generateVerifierToken($username, $email);
    $password_hash = self::createPassword($password);
    $successful = false;
    try {
      $stmt = Common::$database->prepare("
        INSERT INTO `users` (
          `id`, `email`, `username`, `display_name`, `created_datetime`,
          `verified_datetime`, `verifier_token`, `password_hash`,
          `password_salt`, `options_bitmask`, `timezone`
        ) VALUES (
          NULL, :email, :username, :display_name, NOW(),
          NULL, :verifier, :password_hash, NULL, :options_bitmask, NULL
        );
      ");
      $stmt->bindParam(":email", $email, PDO::PARAM_STR);
      $stmt->bindParam(":username", $username, PDO::PARAM_STR);
      $stmt->bindParam(":display_name", $display_name, PDO::PARAM_STR);
      $stmt->bindParam(":verifier", $verifier_token, PDO::PARAM_STR);
      $stmt->bindParam(":password_hash", $password_hash, PDO::PARAM_STR);
      $stmt->bindParam(":options_bitmask", $options_bitmask, PDO::PARAM_INT);
      $successful = $stmt->execute();
      $stmt->closeCursor();
    } catch (PDOException $e) {
      throw new QueryException("Cannot create user", $e);
    } finally {
      return $successful;
    }
  }

  public static function createPassword($password) {
    $cost = Common::$config->bnetdocs->user_password_bcrypt_cost;
    return password_hash($password, PASSWORD_BCRYPT, array('cost' => $cost));
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

  public static function generateVerifierToken($username, $email) {
    // entropy
    $digest = sprintf('%s%s%s', mt_rand(), $username, $email);
    return hash('sha256', $digest);
  }

  public function getAcl($acl) {
    return ($this->options_bitmask & $acl);
  }

  public static function findUserById($user_id) {
    if (is_null($user_id)) return null;

    try {
      return new User($user_id);
    } catch (UserNotFoundException $e) {
      return null;
    }
  }

  public static function &getAllUsers(
    $order = null, $limit = null, $index = null
  ) {
    if (!(is_numeric($limit) || is_numeric($index))) {
      $limit_clause = '';
    } else if (!is_numeric($index)) {
      $limit_clause = 'LIMIT ' . (int) $limit;
    } else {
      $limit_clause = 'LIMIT ' . (int) $index . ',' . (int) $limit;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare('
        SELECT
          `created_datetime`,
          `display_name`,
          `email`,
          `id`,
          `options_bitmask`,
          `password_hash`,
          `password_salt`,
          `timezone`,
          `username`,
          `verified_datetime`,
          `verifier_token`
        FROM `users`
        ORDER BY
          ' . ($order ? '`' . $order[0] . '` ' . $order[1] . ',' : '') . '
          `id` ' . ($order ? $order[1] : 'ASC') . ' ' . $limit_clause . ';'
      );
      if (!$stmt->execute()) {
        throw new QueryException('Cannot refresh all users');
      }
      $objects = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $objects[] = new self($row);
      }
      $stmt->closeCursor();
      return $objects;
    } catch (PDOException $e) {
      throw new QueryException('Cannot refresh all users', $e);
    }
    return null;
  }

  public function getAvatarURI($size) {
    return Common::relativeUrlToAbsolute(
      (new Gravatar($this->getEmail()))->getUrl($size, "identicon")
    );
  }

  public function getCreatedDateTime() {
    if (is_null($this->created_datetime)) {
      return $this->created_datetime;
    } else {
      $tz = new DateTimeZone( 'Etc/UTC' );
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

  public function getURI() {
    return Common::relativeUrlToAbsolute(
      "/user/" . $this->getId() . "/" . Common::sanitizeForUrl(
        $this->getName(), true
      )
    );
  }

  public static function getUserCount() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare('SELECT COUNT(*) FROM `users`;');
      if (!$stmt->execute()) {
        throw new QueryException('Cannot query user count');
      } else if ($stmt->rowCount() == 0) {
        throw new QueryException('Missing result while querying user count');
      }
      $row = $stmt->fetch(PDO::FETCH_NUM);
      $stmt->closeCursor();
      return (int) $row[0];
    } catch (PDOException $e) {
      throw new QueryException('Cannot query user count', $e);
    }
    return null;
  }

  public function getTimezone() {
    return $this->timezone;
  }

  public function getUsername() {
    return $this->username;
  }

  public function getUserProfile() {
    try {
      return new UserProfile($this->id);
    } catch (UserProfileNotFoundException $e) {
      return null;
    }
  }

  public function getVerifiedDateTime() {
    if (is_null($this->verified_datetime)) {
      return $this->verified_datetime;
    } else {
      $tz = new DateTimeZone( 'Etc/UTC' );
      $dt = new DateTime($this->verified_datetime);
      $dt->setTimezone($tz);
      return $dt;
    }
  }

  public function getVerifierToken() {
    return $this->verifier_token;
  }

  public function invalidateVerificationToken() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $stmt = Common::$database->prepare(
      'UPDATE `users` SET `verifier_token` = NULL WHERE `id` = :id LIMIT 1;'
    );

    $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
    $successful = $stmt->execute();

    $stmt->closeCursor();
    return $successful;
  }

  public function isDisabled() {
    return ($this->options_bitmask & self::OPTION_DISABLED);
  }

  public function isStaff() {
    return ($this->options_bitmask & (
      self::OPTION_ACL_DOCUMENT_CREATE  |
      self::OPTION_ACL_DOCUMENT_MODIFY  |
      self::OPTION_ACL_DOCUMENT_DELETE  |
      self::OPTION_ACL_COMMENT_MODIFY   |
      self::OPTION_ACL_COMMENT_DELETE   |
      self::OPTION_ACL_EVENT_LOG_VIEW   |
      self::OPTION_ACL_EVENT_LOG_MODIFY |
      self::OPTION_ACL_EVENT_LOG_DELETE |
      self::OPTION_ACL_NEWS_CREATE      |
      self::OPTION_ACL_NEWS_MODIFY      |
      self::OPTION_ACL_NEWS_DELETE      |
      self::OPTION_ACL_PACKET_CREATE    |
      self::OPTION_ACL_PACKET_MODIFY    |
      self::OPTION_ACL_PACKET_DELETE    |
      self::OPTION_ACL_SERVER_CREATE    |
      self::OPTION_ACL_SERVER_MODIFY    |
      self::OPTION_ACL_SERVER_DELETE    |
      self::OPTION_ACL_USER_CREATE      |
      self::OPTION_ACL_USER_MODIFY      |
      self::OPTION_ACL_USER_DELETE
    ));
  }

  public function isVerified() {
    return ($this->options_bitmask & self::OPTION_VERIFIED);
  }

  public function jsonSerialize() {
    $created_datetime = $this->getCreatedDateTime();
    if (!is_null($created_datetime)) $created_datetime = [
      "iso"  => $created_datetime->format("r"),
      "unix" => $created_datetime->getTimestamp(),
    ];

    $url = Common::relativeUrlToAbsolute(
      "/user/" . $this->getId() . "/"
      . Common::sanitizeForUrl($this->getName())
    );

    $verified_datetime = $this->getVerifiedDateTime();
    if (!is_null($verified_datetime)) $verified_datetime = [
      "iso"  => $verified_datetime->format("r"),
      "unix" => $verified_datetime->getTimestamp(),
    ];

    return [
      "avatar_url"        => $this->getAvatarURI(null),
      "created_datetime"  => $created_datetime,
      "id"                => $this->getId(),
      "name"              => $this->getName(),
      "timezone"          => $this->getTimezone(),
      "url"               => $url,
      "verified_datetime" => $verified_datetime,
    ];
  }

  protected static function normalize(StdClass &$data) {
    $data->created_datetime = (string) $data->created_datetime;
    $data->email            = (string) $data->email;
    $data->id               = (int)    $data->id;
    $data->options_bitmask  = (int)    $data->options_bitmask;
    $data->username         = (string) $data->username;

    if (!is_null($data->display_name))
      $data->display_name = (string) $data->display_name;

    if (!is_null($data->password_hash))
      $data->password_hash = (string) $data->password_hash;

    if (!is_null($data->password_salt))
      $data->password_salt = (string) $data->password_salt;

    if (!is_null($data->timezone))
      $data->timezone = (string) $data->timezone;

    if (!is_null($data->verified_datetime))
      $data->verified_datetime = (string) $data->verified_datetime;

    if (!is_null($data->verifier_token))
      $data->verifier_token = (string) $data->verifier_token;

    return true;
  }

  public function refresh() {
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
          `timezone`,
          `username`,
          `verified_datetime`,
          `verifier_token`
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
      $this->timezone          = $row->timezone;
      $this->username          = $row->username;
      $this->verified_datetime = $row->verified_datetime;
      $this->verifier_token    = $row->verifier_token;
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh user", $e);
    }
    return false;
  }

  public function setAcl($acl, $value) {
    if ($value) {
      $this->options_bitmask |= $acl;
    } else {
      $this->options_bitmask &= ~$acl;
    }
  }

  public function setVerified() {
    $this->invalidateVerificationToken();

    $tz = new DateTimeZone( 'Etc/UTC' );
    $dt = new DateTime($this->created_datetime);
    $dt->setTimezone($tz);

    $verified_datetime = $dt;
    $options_bitmask = $this->options_bitmask | self::OPTION_VERIFIED;

    if ( !isset( Common::$database )) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $successful = false;

    try {

      $stmt = Common::$database->prepare('
        UPDATE `users` SET
          `options_bitmask` = :bits,
          `verified_datetime` = :dt,
          `verifier_token` = NULL
        WHERE `id` = :user_id;
      ');
      $dt = $verified_datetime->format( 'Y-m-d H:i:s' );
      $stmt->bindParam(':dt', $dt, PDO::PARAM_STR); // must be byref
      $stmt->bindParam(':bits', $options_bitmask, PDO::PARAM_INT);
      $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
      $successful = $stmt->execute();
      $stmt->closeCursor();
      if ($successful) {
        $this->options_bitmask = $options_bitmask;
        $this->verified_datetime = $verified_datetime;
        $this->verifier_token = null;
      }

    } catch (PDOException $e) {
      throw new QueryException('Cannot set user as verified', $e);
    } finally {
      return $successful;
    }
  }

}
