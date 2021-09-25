<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Credits;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\Exceptions\UserProfileNotFoundException;
use \BNETDocs\Libraries\IDatabaseObject;
use \BNETDocs\Libraries\UserProfile;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Database;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Gravatar;
use \DateTime;
use \DateTimeZone;
use \Exception;
use \InvalidArgumentException;
use \JsonSerializable;
use \OutOfBoundsException;
use \PDO;
use \PDOException;
use \RuntimeException;
use \StdClass;
use \UnexpectedValueException;

class User implements IDatabaseObject, JsonSerializable
{
  const DATE_SQL = 'Y-m-d H:i:s'; // DateTime::format() string for database

  const DEFAULT_OPTION = self::OPTION_ACL_COMMENT_CREATE;
  const DEFAULT_TZ = 'Etc/UTC';

  // Maximum SQL field lengths, alter as appropriate
  const MAX_DISPLAY_NAME = 0xFF;
  const MAX_EMAIL = 0xFF;
  const MAX_ID = 0x7FFFFFFFFFFFFFFF;
  const MAX_OPTIONS = 0x7FFFFFFFFFFFFFFF;
  const MAX_PASSWORD_HASH = 0xFF;
  const MAX_PASSWORD_SALT = 0xFF;
  const MAX_TIMEZONE = 0xFF;
  const MAX_USERNAME = 0xFF;
  const MAX_VERIFIER_TOKEN = 0xFF;

  const OPTION_DISABLED             = 0x00000001; // User login disabled, active sessions force-expired
  const OPTION_VERIFIED             = 0x00000002; // A token sent via email was returned to us
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
  const OPTION_ACL_PHPINFO          = 0x00800000;

  const TZ_SQL = 'Etc/UTC'; // database values are stored in this TZ

  private $_id;

  protected $created_datetime;
  protected $display_name;
  protected $email;
  protected $id;
  protected $options;
  protected $password_hash;
  protected $password_salt;
  protected $record_updated;
  protected $timezone;
  protected $username;
  protected $verified_datetime;
  protected $verifier_token;

  public function __construct($value)
  {
    if (is_string($value) && is_numeric($value) && strpos($value, '.') === false)
    {
      // something is lazily providing an int value in a string type
      $value = (int) $value;
    }

    if (is_null($value) || is_int($value))
    {
      $this->_id = $value;
      $this->allocate();
      return;
    }

    if ($value instanceof StdClass)
    {
      $this->allocateObject($value);
      return;
    }

    throw new InvalidArgumentException(sprintf(
      'value must be null, an integer, or StdClass; %s given', gettype($value)
    ));
  }

  /**
   * Implements the allocate function from the IDatabaseObject interface
   */
  public function allocate()
  {
    $id = $this->_id;

    if (!(is_null($id) || is_int($id)))
    {
      throw new InvalidArgumentException('value must be null or an integer');
    }

    $this->setCreatedDateTime(new DateTime('now'));
    $this->setId($id);
    $this->setOptions(self::DEFAULT_OPTION);
    $this->setRecordUpdated(new DateTime('now'));
    $this->setTimezone(self::DEFAULT_TZ);

    if (is_null($id)) return;

    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      SELECT
        `created_datetime`,
        `display_name`,
        `email`,
        `id`,
        `options_bitmask`,
        `password_hash`,
        `password_salt`,
        `record_updated`,
        `timezone`,
        `username`,
        `verified_datetime`,
        `verifier_token`
      FROM `users` WHERE `id` = :id LIMIT 1;
    ');
    $q->bindParam(':id', $id, PDO::PARAM_INT);

    $r = $q->execute();
    if (!$r)
    {
      throw new UnexpectedValueException(sprintf('an error occurred finding user id: %d', $id));
    }

    if ($q->rowCount() != 1)
    {
      throw new UnexpectedValueException(sprintf('user id: %d not found', $id));
    }

    $r = $q->fetchObject();
    $q->closeCursor();

    $this->allocateObject($r);
  }

  /**
   * Internal function to process and translate StdClass objects into properties.
   */
  protected function allocateObject(StdClass $value)
  {
    $tz = new DateTimeZone(self::TZ_SQL);

    $this->setCreatedDateTime(new DateTime($value->created_datetime, $tz));
    $this->setDisplayName($value->display_name);
    $this->setEmail($value->email);
    $this->setId($value->id);
    $this->setOptions($value->options_bitmask);
    $this->setPasswordHash($value->password_hash);
    $this->setPasswordSalt($value->password_salt);
    $this->setRecordUpdated(new DateTime($value->record_updated, $tz));
    $this->setTimezone($value->timezone);
    $this->setUsername($value->username);
    $this->setVerifiedDateTime(
      $value->verified_datetime ? new DateTime($value->verified_datetime, $tz) : null
    );
    $this->setVerifierToken($value->verifier_token);
  }

  /**
   * Implements the commit function from the IDatabaseObject interface
   */
  public function commit()
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare(
      'INSERT INTO `users` (
        `created_datetime`,
        `display_name`,
        `email`,
        `id`,
        `options_bitmask`,
        `password_hash`,
        `password_salt`,
        `record_updated`,
        `timezone`,
        `username`,
        `verified_datetime`,
        `verifier_token`
      ) VALUES (
        :c_dt, :d_name, :email, :id, :opts, :p_hash, :p_salt, :rec_up_dt, :tz, :u_name, :v_dt, :v_t
      ) ON DUPLICATE KEY UPDATE
        `created_datetime` = :c_dt,
        `display_name` = :d_name,
        `email` = :email,
        `id` = :id,
        `options_bitmask` = :opts,
        `password_hash` = :p_hash,
        `password_salt` = :p_salt,
        `record_updated` = :rec_up_dt,
        `timezone` = :tz,
        `username` = :u_name,
        `verified_datetime` = :v_dt,
        `verifier_token` = :v_t
      ;'
    );

    $this->setRecordUpdated(new DateTime('now', new DateTimeZone(self::TZ_SQL)));

    $created_datetime = $this->created_datetime->format(self::DATE_SQL);
    $record_updated = $this->record_updated->format(self::DATE_SQL);

    $verified_datetime = (
      is_null($this->verified_datetime) ? null : $this->verified_datetime->format(self::DATE_SQL)
    );

    $q->bindParam(':c_dt', $created_datetime, PDO::PARAM_STR);
    $q->bindParam(':d_name', $this->display_name, (is_null($this->display_name) ? PDO::PARAM_NULL : PDO::PARAM_STR));
    $q->bindParam(':email', $this->email, PDO::PARAM_STR);
    $q->bindParam(':id', $this->id, (is_null($this->id) ? PDO::PARAM_NULL : PDO::PARAM_INT));
    $q->bindParam(':opts', $this->options, PDO::PARAM_INT);
    $q->bindParam(':p_hash', $this->password_hash, (is_null($this->password_hash) ? PDO::PARAM_NULL : PDO::PARAM_STR));
    $q->bindParam(':p_salt', $this->password_salt, (is_null($this->password_salt) ? PDO::PARAM_NULL : PDO::PARAM_STR));
    $q->bindParam(':rec_up_dt', $record_updated, PDO::PARAM_STR);
    $q->bindParam(':tz', $this->timezone, (is_null($this->timezone) ? PDO::PARAM_NULL : PDO::PARAM_STR));
    $q->bindParam(':u_name', $this->username, PDO::PARAM_STR);
    $q->bindParam(':v_dt', $verified_datetime, (is_null($verified_datetime) ? PDO::PARAM_NULL : PDO::PARAM_STR));
    $q->bindParam(':v_t', $this->verifier_token, (is_null($this->verifier_token) ? PDO::PARAM_NULL : PDO::PARAM_STR));

    $r = $q->execute();
    if (!$r) return $r;

    $q->closeCursor();

    $q = Common::$database->prepare('SELECT `id` FROM `users` WHERE `username` = :u_name LIMIT 1;');
    $q->bindParam(':u_name', $this->username, PDO::PARAM_STR);

    $r = $q->execute();
    if (!$r) return $r;

    $this->setId($q->fetch(PDO::FETCH_NUM)[0]);

    $q->closeCursor();
    return $r;
  }

  public function checkPassword(string $password)
  {
    if (is_null($this->password_hash))
    {
      // no hash set
      return false;
    }

    if (substr($this->password_hash, 0, 1) == '$')
    {
      // new style bcrypt password
      // salt and pepper are deprecated and unused here

      $cost = Common::$config->bnetdocs->user_password_bcrypt_cost;
      $match = password_verify($password, $this->password_hash);
      $rehash = password_needs_rehash(
        $this->password_hash, PASSWORD_BCRYPT, array('cost' => $cost)
      );

      return ($match && !$rehash); // will deny if not match or needs rehash
    }
    else
    {
      // old style peppered and salted sha256 password

      $pepper = Common::$config->bnetdocs->user_password_pepper;
      $salt = $this->password_salt;

      if (is_null($salt))
      {
        // no salt set
        return false;
      }

      $hash = strtoupper(hash('sha256', $password.$salt.$pepper));
      return ($hash === strtoupper($this->password_hash));
    }
  }

  public static function createPassword(string $password)
  {
    $cost = Common::$config->bnetdocs->user_password_bcrypt_cost;
    return password_hash($password, PASSWORD_BCRYPT, array('cost' => $cost));
  }

  public static function findIdByEmail(string $email)
  {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
      throw new UnexpectedValueException('email is not a valid email address');
    }

    if (!isset(Common::$database)) Common::$database = DatabaseDriver::getDatabaseObject();

    $q = Common::$database->prepare(
      'SELECT `id` FROM `users` WHERE `email` = :email LIMIT 1;'
    );
    $q->bindParam(':email', $email, PDO::PARAM_STR);

    if (!$q->execute()) return false;
    if ($q->rowCount() == 0) throw new UserNotFoundException($email);

    $r = $q->fetch(PDO::FETCH_NUM);
    return (int) $r[0];
  }

  public static function findIdByUsername(string $username)
  {
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

  public static function generateVerifierToken(string $username, string $email)
  {
    // entropy
    $digest = sprintf('%s%s%s', mt_rand(), $username, $email);
    return hash('sha256', $digest);
  }

  public static function &getAllUsers($order = null, $limit = null, $index = null)
  {
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
          `record_updated`,
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

  public function getAvatarURI($size)
  {
    return Common::relativeUrlToAbsolute(
      (new Gravatar($this->getEmail()))->getUrl($size, 'identicon')
    );
  }

  public function getCreatedDateTime()
  {
    return $this->created_datetime;
  }

  public function getCreatedEstimate() {
    $c = $this->getCreatedDateTime();
    if (!$c) return $c;

    $now = new DateTime('now');
    $d = $c->diff($now);
    $i = 0;
    $r = '';
    $t = 3;

    if ($d->y > 0 && $i < $t) { ++$i; $r .= sprintf('%s%d %s%s', ($r ? ', ' : ''), $d->y, 'year', ($d->y !== 1 ? 's' : '')); }
    if ($d->m > 0 && $i < $t) { ++$i; $r .= sprintf('%s%d %s%s', ($r ? ', ' : ''), $d->m, 'month', ($d->m !== 1 ? 's' : '')); }
    if ($d->d > 0 && $i < $t) { ++$i; $r .= sprintf('%s%d %s%s', ($r ? ', ' : ''), $d->d, 'day', ($d->d !== 1 ? 's' : '')); }
    if ($d->h > 0 && $i < $t) { ++$i; $r .= sprintf('%s%d %s%s', ($r ? ', ' : ''), $d->h, 'hour', ($d->h !== 1 ? 's' : '')); }
    if ($d->i > 0 && $i < $t) { ++$i; $r .= sprintf('%s%d %s%s', ($r ? ', ' : ''), $d->i, 'minute', ($d->i !== 1 ? 's' : '')); }

    if ($i === 0) $r = 'created a moment ago';
    if ($c > $now) $r = '-' . $r;

    return $r;
  }

  public function getDisplayName()
  {
    return $this->display_name;
  }

  public function getEmail()
  {
    return $this->email;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getName()
  {
    return $this->display_name ?? $this->username;
  }

  public function getOption(int $option)
  {
    if ($option < 0 || $option > self::MAX_OPTIONS)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_OPTIONS
      ));
    }

    return ($this->options & $option) === $option;
  }

  public function getOptions()
  {
    return $this->options;
  }

  public function getPasswordHash()
  {
    return $this->password_hash;
  }

  public function getPasswordSalt()
  {
    return $this->password_salt;
  }

  public function getRecordUpdated()
  {
    return $this->record_updated;
  }

  public function getURI()
  {
    $id = $this->getId();

    if (is_null($id))
    {
      throw new UnexpectedValueException('user id is null');
    }

    return Common::relativeUrlToAbsolute(sprintf(
      '/user/%s/%s', $id, Common::sanitizeForUrl($this->getName(), true)
    ));
  }

  public static function getUserCount()
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('SELECT COUNT(*) FROM `users`;');
    $r = $q->execute();

    if (!$r || $q->rowCount() !== 1)
    {
      return false;
    }

    $r = $q->fetch(PDO::FETCH_NUM);
    $q->closeCursor();

    return (int) $r[0];
  }

  public function getTimezone()
  {
    return $this->timezone;
  }

  public function getUsername()
  {
    return $this->username;
  }

  public function getUserProfile()
  {
    try
    {
      return new UserProfile($this->id);
    }
    catch (UserProfileNotFoundException $e)
    {
      return null;
    }
  }

  public function getVerifiedDateTime()
  {
    return $this->verified_datetime;
  }

  public function getVerifierToken()
  {
    return $this->verifier_token;
  }

  public function isDisabled()
  {
    return $this->getOption(self::OPTION_DISABLED);
  }

  public function isStaff()
  {
    return ($this->options & (
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

  public function isVerified()
  {
    return $this->getOption(self::OPTION_VERIFIED);
  }

  public function jsonSerialize()
  {
    return [
      'avatar_url' => $this->getAvatarURI(null),
      'id' => $this->getId(),
      'member_for' => $this->getCreatedEstimate(),
      'name' => $this->getName(),
      'timezone' => $this->getTimezone(),
      'url' => $this->getURI(),
    ];
  }

  public function setCreatedDateTime(DateTime $value)
  {
    $this->created_datetime = $value;
  }

  public function setDisplayName(?string $value)
  {
    if (!is_null($value) && (empty($value) || strlen($value) > self::MAX_DISPLAY_NAME))
    {
      throw new OutOfBoundsException(sprintf(
        'value must be null or between 1-%d characters', self::MAX_DISPLAY_NAME
      ));
    }

    $this->display_name = $value;
  }

  public function setEmail(string $value)
  {
    if (empty($value) || strlen($value) > self::MAX_EMAIL)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 1-%d characters', self::MAX_EMAIL
      ));
    }

    if (!filter_var($value, FILTER_VALIDATE_EMAIL))
    {
      throw new UnexpectedValueException('value is not a valid email address');
    }

    $this->email = $value;
  }

  public function setId(?int $value)
  {
    if (!is_null($value) && ($value < 0 || $value > self::MAX_ID))
    {
      throw new InvalidArgumentException(sprintf(
        'value must be between 0-%d', self::MAX_ID
      ));
    }

    $this->id = $value;
  }

  public function setOption(int $option, bool $value)
  {
    if ($option < 0 || $option > self::MAX_OPTIONS)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_OPTIONS
      ));
    }

    if ($value)
    {
      $this->options |= $option; // bitwise or
    }
    else
    {
      $this->options &= ~$option; // bitwise and ones complement
    }
  }

  public function setOptions(int $value)
  {
    if ($value < 0 || $value > self::MAX_OPTIONS)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_OPTIONS
      ));
    }

    $this->options = $value;
  }

  public function setPassword(string $value)
  {
    $this->setPasswordHash(self::createPassword($value));
    $this->setPasswordSalt(null);
  }

  public function setPasswordHash(?string $value)
  {
    if (!is_null($value) && (empty($value) || strlen($value) > self::MAX_PASSWORD_HASH))
    {
      throw new OutOfBoundsException(sprintf(
        'value must be null or between 1-%d characters', self::MAX_PASSWORD_HASH
      ));
    }

    $this->password_hash = $value;
  }

  public function setPasswordSalt(?string $value)
  {
    if (!is_null($value) && (empty($value) || strlen($value) > self::MAX_PASSWORD_SALT))
    {
      throw new OutOfBoundsException(sprintf(
        'value must be null or between 1-%d characters', self::MAX_PASSWORD_SALT
      ));
    }

    $this->password_salt = $value;
  }

  public function setRecordUpdated(DateTime $value)
  {
    $this->record_updated = $value;
  }

  public function setTimezone(?string $value)
  {
    if (!is_null($value) && strlen($value) > self::MAX_TIMEZONE)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be null or between 0-%d characters', self::MAX_TIMEZONE
      ));
    }

    if (!empty($value))
    {
      try
      {
        $tz = new DateTimeZone($value);
        if (!$tz) throw new RuntimeException();
      }
      catch (Exception $e)
      {
        throw new UnexpectedValueException('value must be a valid timezone', $e);
      }
    }

    $this->timezone = $value;
  }

  public function setUsername(string $value)
  {
    if (empty($value) || strlen($value) > self::MAX_USERNAME)
    {
      throw new OutOfBoundsException(sprintf(
        'value must be between 1-%d characters', self::MAX_USERNAME
      ));
    }

    $this->username = $value;
  }

  public function setVerified(bool $value, bool $reset = false)
  {
    $old_value = $this->getOption(self::OPTION_VERIFIED);
    if (!$reset && $old_value === $value) return; // avoid resetting values every call

    $this->setOption(self::OPTION_VERIFIED, $value);

    if ($value)
    {
      // verified
      $this->setVerifiedDateTime(new DateTime('now'));
      $this->setVerifierToken(null);
    }
    else
    {
      // not verified
      $this->setVerifiedDateTime(null);
      $this->setVerifierToken(self::generateVerifierToken($this->username ?? '', $this->email ?? ''));
    }
  }

  public function setVerifiedDateTime(?DateTime $value)
  {
    $this->verified_datetime = $value;
  }

  public function setVerifierToken(?string $value)
  {
    if (!is_null($value) && (empty($value) || strlen($value) > self::MAX_VERIFIER_TOKEN))
    {
      throw new OutOfBoundsException(sprintf(
        'value must be null or between 1-%d characters', self::MAX_VERIFIER_TOKEN
      ));
    }

    $this->verifier_token = $value;
  }
}
