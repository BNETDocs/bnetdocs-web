<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DateTimeImmutable;
use \BNETDocs\Libraries\UserProfile;
use \CarlBennett\MVC\Libraries\Common;
use \DateTimeInterface;
use \DateTimeZone;
use \OutOfBoundsException;
use \StdClass;
use \UnexpectedValueException;

class User implements \BNETDocs\Interfaces\DatabaseObject, \JsonSerializable
{
  public const DEFAULT_OPTION = self::OPTION_ACL_COMMENT_CREATE;
  public const DEFAULT_TZ = null; // null means no timezone preference/automatic.

  // Maximum SQL field lengths, alter as appropriate
  public const MAX_DISPLAY_NAME = 0xFF;
  public const MAX_EMAIL = 0xFF;
  public const MAX_ID = 0x7FFFFFFFFFFFFFFF;
  public const MAX_OPTIONS = 0x7FFFFFFFFFFFFFFF;
  public const MAX_PASSWORD_HASH = 0xFF;
  public const MAX_PASSWORD_SALT = 0xFF;
  public const MAX_TIMEZONE = 0xFF;
  public const MAX_USERNAME = 0xFF;
  public const MAX_VERIFIER_TOKEN = 0xFF;

  public const OPTION_DISABLED             = 0x00000001; // User login disabled, active sessions force-expired
  public const OPTION_VERIFIED             = 0x00000002; // A token sent via email was returned to us
  public const OPTION_ACL_DOCUMENT_CREATE  = 0x00000004;
  public const OPTION_ACL_DOCUMENT_MODIFY  = 0x00000008;
  public const OPTION_ACL_DOCUMENT_DELETE  = 0x00000010;
  public const OPTION_ACL_COMMENT_CREATE   = 0x00000020;
  public const OPTION_ACL_COMMENT_MODIFY   = 0x00000040;
  public const OPTION_ACL_COMMENT_DELETE   = 0x00000080;
  public const OPTION_ACL_EVENT_LOG_VIEW   = 0x00000100;
  public const OPTION_ACL_EVENT_LOG_MODIFY = 0x00000200;
  public const OPTION_ACL_EVENT_LOG_DELETE = 0x00000400;
  public const OPTION_ACL_NEWS_CREATE      = 0x00000800;
  public const OPTION_ACL_NEWS_MODIFY      = 0x00001000;
  public const OPTION_ACL_NEWS_DELETE      = 0x00002000;
  public const OPTION_ACL_PACKET_CREATE    = 0x00004000;
  public const OPTION_ACL_PACKET_MODIFY    = 0x00008000;
  public const OPTION_ACL_PACKET_DELETE    = 0x00010000;
  public const OPTION_ACL_SERVER_CREATE    = 0x00020000;
  public const OPTION_ACL_SERVER_MODIFY    = 0x00040000;
  public const OPTION_ACL_SERVER_DELETE    = 0x00080000;
  public const OPTION_ACL_USER_CREATE      = 0x00100000;
  public const OPTION_ACL_USER_MODIFY      = 0x00200000;
  public const OPTION_ACL_USER_DELETE      = 0x00400000;
  public const OPTION_ACL_PHPINFO          = 0x00800000;

  protected DateTimeInterface $created_datetime;
  protected ?string $display_name;
  protected string $email;
  protected ?int $id;
  protected int $options;
  protected ?string $password_hash;
  protected ?string $password_salt;
  protected DateTimeInterface $record_updated;
  protected ?string $timezone;
  protected string $username;
  protected ?DateTimeInterface $verified_datetime;
  protected ?string $verifier_token;

  /**
   * Constructs a User object from properties, a user id to lookup, or null for a new record.
   *
   * @param StdClass|integer|null $value Object properties, user id, or null.
   */
  public function __construct(StdClass|int|null $value)
  {
    if ($value instanceof StdClass)
    {
      $this->allocateObject($value);
    }
    else
    {
      $this->setId($value);
      if (!$this->allocate()) throw new \BNETDocs\Exceptions\UserNotFoundException($this);
    }
  }

  /**
   * Allocates the properties of this object from the database.
   *
   * @return boolean Whether the operation was successful.
   */
  public function allocate() : bool
  {
    // Set initial property values, but skip the id property.
    $this->setCreatedDateTime(new DateTimeImmutable('now'));
    $this->setDisplayName(null);
    $this->setEmail('', true);
    $this->setOptions(self::DEFAULT_OPTION);
    $this->setPasswordHash(null);
    $this->setPasswordSalt(null);
    $this->setRecordUpdated(new DateTimeImmutable('now'));
    $this->setTimezone(self::DEFAULT_TZ);
    $this->setUsername('', true);
    $this->setVerifiedDateTime(null);
    $this->setVerifierToken(null);

    // Get database record only if the id property is not null.
    $id = $this->getId();
    if (is_null($id)) return true;

    $q = Database::instance()->prepare('
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
    if (!$q || !$q->execute([':id' => $id]) || $q->rowCount() != 1) return false;
    $this->allocateObject($q->fetchObject());
    $q->closeCursor();
    return true;
  }

  /**
   * Internal function to process and translate StdClass objects into properties.
   */
  protected function allocateObject(StdClass $value) : void
  {
    $this->setCreatedDateTime($value->created_datetime);
    $this->setDisplayName($value->display_name);
    $this->setEmail($value->email);
    $this->setId($value->id);
    $this->setOptions($value->options_bitmask);
    $this->setPasswordHash($value->password_hash);
    $this->setPasswordSalt($value->password_salt);
    $this->setRecordUpdated($value->record_updated);
    $this->setTimezone($value->timezone);
    $this->setUsername($value->username);
    $this->setVerifiedDateTime($value->verified_datetime);
    $this->setVerifierToken($value->verifier_token);
  }

  /**
   * Commits the properties of this object to the database.
   *
   * @return boolean Whether the operation was successful.
   */
  public function commit() : bool
  {
    $q = Database::instance()->prepare('
      INSERT INTO `users` (
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
        :cdt, :dn, :e, :id, :o, :pwh, :pws, :rudt, :tz, :u, :vdt, :vt
      ) ON DUPLICATE KEY UPDATE
        `created_datetime` = :cdt,
        `display_name` = :dn,
        `email` = :e,
        `id` = :id,
        `options_bitmask` = :o,
        `password_hash` = :pwh,
        `password_salt` = :pws,
        `record_updated` = :rudt,
        `timezone` = :tz,
        `username` = :u,
        `verified_datetime` = :vdt,
        `verifier_token` = :vt;
    ');

    $this->setRecordUpdated(new DateTimeImmutable('now', new DateTimeZone(self::DATE_TZ)));

    $p = [
      ':cdt' => $this->getCreatedDateTime(),
      ':dn' => $this->getDisplayName(),
      ':e' => $this->getEmail(),
      ':id' => $this->getId(),
      ':o' => $this->getOptions(),
      ':pwh' => $this->getPasswordHash(),
      ':pws' => $this->getPasswordSalt(),
      ':rudt' => $this->getRecordUpdated(),
      ':tz' => $this->getTimezone(),
      ':u' => $this->getUsername(),
      ':vdt' => $this->getVerifiedDateTime(),
      ':vt' => $this->getVerifierToken(),
    ];

    foreach ($p as $k => $v)
      if ($v instanceof DateTimeInterface)
        $p[$k] = $v->format(self::DATE_SQL);

    if (!$q || !$q->execute($p)) return false;
    if (is_null($p[':id'])) $this->setId(Database::instance()->lastInsertId());
    $q->closeCursor();
    return true;
  }

  /**
   * Checks whether this user's password matches or not against cleartext input.
   * The match will always fail if this user's password needs rehashing.
   *
   * @param string $password The cleartext input.
   * @return boolean Whether this user's password matches.
   */
  public function checkPassword(string $password) : bool // This function was refactored by OpenAI
  {
    if (is_null($this->password_hash)) return false;

    if (substr($this->password_hash, 0, 1) == '$') {
      // new style bcrypt password
      $cost = Common::$config->bnetdocs->user_password_bcrypt_cost;
      $match = password_verify($password, $this->password_hash);
      $rehash = password_needs_rehash(
        $this->password_hash, PASSWORD_BCRYPT, array('cost' => $cost)
      );

      return $match && !$rehash;
    } else {
      // old style peppered and salted sha256 password
      $pepper = Common::$config->bnetdocs->user_password_pepper;
      $salt = $this->password_salt;

      if (is_null($salt)) return false;

      $hash = strtoupper(hash('sha256', $password.$salt.$pepper));
      return strtoupper($this->password_hash) === $hash;
    }
  }

  /**
   * Creates a password bcrypt hash from a cleartext password input.
   *
   * @param string $password The cleartext password.
   * @return string The hashed password.
   */
  public static function createPassword(string $password) : string
  {
    $cost = Common::$config->bnetdocs->user_password_bcrypt_cost;
    return password_hash($password, PASSWORD_BCRYPT, array('cost' => $cost));
  }

  /**
   * Deallocates the properties of this object from the database.
   *
   * @return boolean Whether the operation was successful.
   */
  public function deallocate() : bool
  {
    $id = $this->getId();
    if (is_null($id)) return false;
    $q = Database::instance()->prepare('DELETE FROM `users` WHERE `id` = ? LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { $q->closeCursor(); }
  }

  /**
   * Retrieves the user id of a user record by email address.
   *
   * @param string $value The email address.
   * @return integer|null The user id of the user, or null if not found.
   */
  public static function findIdByEmail(string $value) : ?int
  {
    if (!filter_var($value, FILTER_VALIDATE_EMAIL))
      throw new UnexpectedValueException('email is not formatted as a valid email address');

    $q = Database::instance()->prepare('SELECT `id` FROM `users` WHERE `email` = ? LIMIT 1;');
    if (!$q || !$q->execute([$value]) || $q->rowCount() == 0) return null;
    $r = $q->fetchObject();
    $q->closeCursor();
    return (int) $r->id;
  }

  /**
   * Retrieves the user id of a user record by username.
   *
   * @param string $value The username.
   * @return integer|null The user id of the user, or null if not found.
   */
  public static function findIdByUsername(string $value) : ?int
  {
    $q = Database::instance()->prepare('SELECT `id` FROM `users` WHERE `username` = ? LIMIT 1;');
    if (!$q || !$q->execute([$value]) || $q->rowCount() == 0) return null;
    $r = $q->fetchObject();
    $q->closeCursor();
    return (int) $r->id;
  }

  /**
   * Generates a verifier token for use with setVerifierToken().
   *
   * @param string $username The username, for entropy.
   * @param string $email The email address, for entropy.
   * @return string The verifier token.
   */
  public static function generateVerifierToken(string $username, string $email) : string
  {
    return hash('sha256', sprintf('%s%s%s', mt_rand(), $username, $email));
  }

  /**
   * Retrieves all possible User record objects as one array.
   *
   * @param array|null $order The column to order by.
   * @param integer|null $limit The limit of records to return.
   * @param integer|null $index The starting index of records to return, as limited by $limit.
   * @return array|null The User object records, or null on error.
   */
  public static function &getAllUsers(?array $order = null, ?int $limit = null, ?int $index = null) : ?array
  {
    if (!is_null($limit) && !is_null($index))
      $limit_clause = sprintf('LIMIT %d,%d', $index, $limit);
    else if (!is_null($limit))
      $limit_clause = sprintf('LIMIT %d', $limit);
    else
      $limit_clause = '';

    $q = Database::instance()->prepare(sprintf('
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
      FROM `users` ORDER BY %s %s;
    ', (
      $order ? (sprintf('`%s` %s, `id` %s', $order[0], $order[1], $order[1])) : '`id` ASC'
    ), $limit_clause));
    if (!$q || !$q->execute()) return null;

    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  /**
   * Retrieves the avatar thumbnail url for this user. Requires the email address to be set.
   *
   * @param integer|null $size The pixel width & height size of the desired avatar thumbnail.
   * @return string The avatar thumbnail url.
   */
  public function getAvatarURI(?int $size) : string
  {
    return Common::relativeUrlToAbsolute(
      (new \BNETDocs\Libraries\Gravatar($this->getEmail()))->getUrl($size, 'identicon')
    );
  }

  /**
   * Retrieves the Date & Time this user record was created.
   *
   * @return DateTimeInterface|null The Date & Time interface-compatible value.
   */
  public function getCreatedDateTime() : ?DateTimeInterface
  {
    return $this->created_datetime;
  }

  /**
   * Retrieves the fuzzy estimate Date & Time this user record was created.
   * Useful for printing in public spaces where the exact moment is less important than a vague when.
   *
   * @return string|null The relative Date & Time string.
   */
  public function getCreatedEstimate() : string
  {
    $c = $this->getCreatedDateTime();
    $now = new DateTimeImmutable('now');
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

  /**
   * Retrieves the display name of this user.
   *
   * @return string|null The display name, or null if not set.
   */
  public function getDisplayName() : ?string
  {
    return $this->display_name;
  }

  /**
   * Retrieves the email address for this user.
   *
   * @return string The email address.
   */
  public function getEmail() : string
  {
    return $this->email;
  }

  /**
   * Retrieves the id for this user, or null if not yet committed to the database.
   *
   * @return integer|null The id.
   */
  public function getId() : ?int
  {
    return $this->id;
  }

  /**
   * Retrieves the printable name for this user, taking into consideration username and display name.
   *
   * @return string The display name, or if null, then the username.
   */
  public function getName() : string
  {
    return $this->display_name ?? $this->username;
  }

  /**
   * Retrieves an option in the options bitmask for this user.
   *
   * @param integer $option One of the OPTION_* constants.
   * @return boolean Whether the option is set (true) or unset (false).
   * @throws OutOfBoundsException if option must be between 0-MAX_OPTIONS.
   */
  public function getOption(int $option) : bool
  {
    if ($option < 0 || $option > self::MAX_OPTIONS)
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_OPTIONS
      ));

    return ($this->options & $option) === $option;
  }

  /**
   * Retrieves the options bitmask for this user.
   *
   * @return integer The options bitmask, that are set (1) or unset (0).
   */
  public function getOptions() : int
  {
    return $this->options;
  }

  /**
   * Retrieves the password hash for this user, or null for no password hash set.
   *
   * @return string|null The password hash.
   */
  public function getPasswordHash() : ?string
  {
    return $this->password_hash;
  }

  /**
   * Retrieves the password salt for this user, or null for no password salt set.
   * Note: Salt is deprecated, this is now always set null for new passwords.
   *
   * @return string|null The password salt.
   */
  public function getPasswordSalt() : ?string
  {
    return $this->password_salt;
  }

  /**
   * Retrieves the Date & Time this user record was last updated.
   *
   * @return DateTimeInterface The Date & Time interface-compatible value.
   */
  public function getRecordUpdated() : DateTimeInterface
  {
    return $this->record_updated;
  }

  /**
   * Retrieves the unique URL for this user.
   *
   * @return string The URL.
   * @throws UnexpectedValueException when this user's id property is null.
   */
  public function getURI() : string
  {
    $id = $this->getId();
    if (is_null($id)) throw new UnexpectedValueException('user id is null');

    return Common::relativeUrlToAbsolute(sprintf(
      '/user/%s/%s', $id, Common::sanitizeForUrl($this->getName(), true)
    ));
  }

  /**
   * Retrieves the total number of registered users.
   *
   * @return integer|false The count, or false on error.
   */
  public static function getUserCount() : int|false
  {
    $q = Database::instance()->prepare('SELECT COUNT(*) AS `count` FROM `users`;');
    if (!$q || !$q->execute() || $q->rowCount() != 1) return false;
    $r = (int) $q->fetchObject()->count;
    $q->closeCursor();
    return $r;
  }

  /**
   * Retrieves the preferred timezone for this user, or null for no timezone preference/automatic.
   *
   * @return string|null The timezone.
   */
  public function getTimezone() : ?string
  {
    return $this->timezone;
  }

  /**
   * Retrieves the username for this user.
   *
   * @return string The username.
   */
  public function getUsername() : string
  {
    return $this->username;
  }

  /**
   * Retrieves the UserProfile record for this user, or null if no profile record.
   *
   * @return UserProfile|null
   */
  public function getUserProfile() : ?UserProfile
  {
    try
    {
      return new UserProfile($this->id);
    }
    catch (UnexpectedValueException $e)
    {
      return null;
    }
  }

  /**
   * Retrieves the Date & Time this user record was verified.
   *
   * @return string The Date & Time interface-compatible value.
   */
  public function getVerifiedDateTime() : ?DateTimeInterface
  {
    return $this->verified_datetime;
  }

  /**
   * Retrieves this user's verifier token value.
   *
   * @return string The verifier token.
   */
  public function getVerifierToken() : ?string
  {
    return $this->verifier_token;
  }

  /**
   * Retrieves whether this user is disabled administratively a.k.a. banned.
   *
   * @return boolean Whether OPTION_DISABLED is set or unset in this user's options bitmask.
   */
  public function isDisabled() : bool
  {
    return $this->getOption(self::OPTION_DISABLED);
  }

  /**
   * Retrieves whether this user is considered a staff member or not.
   *
   * @return boolean Whether a select few OPTION_ACL_* are set in this user's options bitmask.
   */
  public function isStaff() : bool
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

  /**
   * Retrieves whether this user has the verified status or not.
   *
   * @return boolean Whether OPTION_VERIFIED is set or unset in this user's options bitmask.
   */
  public function isVerified() : bool
  {
    return $this->getOption(self::OPTION_VERIFIED);
  }

  /**
   * Serializes this object's properties. Part of JsonSerializable interface.
   *
   * @return mixed The serialized value.
   */
  public function jsonSerialize() : mixed
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

  /**
   * Sets the Date & Time this user record was created.
   *
   * @param DateTimeInterface|string $value The Date & Time interface-compatible value.
   * @return void
   */
  public function setCreatedDateTime(DateTimeInterface|string $value) : void
  {
    $this->created_datetime = (is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value
    );
  }

  /**
   * Sets the display name for this user, displayed instead of the username.
   *
   * @param string|null $value The display name, or null to display the username instead.
   * @return void
   * @throws OutOfBoundsException if value must be null or between 1-MAX_DISPLAY_NAME characters.
   */
  public function setDisplayName(?string $value) : void
  {
    if (!is_null($value) && (empty($value) || strlen($value) > self::MAX_DISPLAY_NAME))
      throw new OutOfBoundsException(sprintf(
        'value must be null or between 1-%d characters', self::MAX_DISPLAY_NAME
      ));

    $this->display_name = $value;
  }

  /**
   * Sets the email address for this user.
   *
   * @param string $value The email address.
   * @param boolean $ignore_empty Whether an empty value should be accepted, defaults false.
   * @return void
   * @throws OutOfBoundsException if value must be between 1-MAX_EMAIL characters.
   * @throws UnexpectedValueException if value is not formatted as a valid email address.
   */
  public function setEmail(string $value, bool $ignore_empty = false) : void
  {
    if ((!$ignore_empty && empty($value)) || strlen($value) > self::MAX_EMAIL)
      throw new OutOfBoundsException(sprintf(
        'value must be between 1-%d characters', self::MAX_EMAIL
      ));

    if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL))
      throw new UnexpectedValueException('value is not formatted as a valid email address');

    $this->email = $value;
  }

  /**
   * Sets the id for this user, or null for a new user not yet committed to the database.
   *
   * @param integer|null $value The id.
   * @return void
   * @throws OutOfBoundsException if value must be null or between 0-MAX_ID.
   */
  public function setId(?int $value) : void
  {
    if (!is_null($value) && ($value < 0 || $value > self::MAX_ID))
      throw new OutOfBoundsException(sprintf(
        'value must be null or an integer between 0-%d', self::MAX_ID
      ));

    $this->id = $value;
  }

  /**
   * Toggles an option in the options bitmask for this user.
   *
   * @param integer $option One of the OPTION_* constants.
   * @param boolean $value Whether it should be set (true) or unset (false).
   * @return void
   * @throws OutOfBoundsException if option must be between 0-MAX_OPTIONS.
   */
  public function setOption(int $option, bool $value) : void
  {
    if ($option < 0 || $option > self::MAX_OPTIONS)
      throw new OutOfBoundsException(sprintf(
        'value must be between 0-%d', self::MAX_OPTIONS
      ));

    if ($value) $this->options |= $option; // bitwise or
    else $this->options &= ~$option; // bitwise and ones complement
  }

  /**
   * Sets the options bitmask for this user.
   *
   * @param integer $value The options bitmask, that are set (1) or unset (0).
   * @return void
   * @throws OutOfBoundsException if option must be between 0-MAX_OPTIONS.
   */
  public function setOptions(int $value) : void
  {
    if ($value < 0 || $value > self::MAX_OPTIONS)
      throw new OutOfBoundsException(sprintf(
        'value must be an integer between 0-%d', self::MAX_OPTIONS
      ));

    $this->options = $value;
  }

  /**
   * Sets the password hash and salt for this user from cleartext user input.
   *
   * @param string $value The cleartext password.
   * @return void
   */
  public function setPassword(string $value) : void
  {
    $this->setPasswordHash(self::createPassword($value));
    $this->setPasswordSalt(null); // Deprecated
  }

  /**
   * Sets the password hash for this user.
   *
   * @param string|null $value The password hash.
   * @return void
   * @throws OutOfBoundsException if value must be null or between 1-MAX_PASSWORD_HASH characters.
   */
  public function setPasswordHash(?string $value) : void
  {
    if (!is_null($value) && (empty($value) || strlen($value) > self::MAX_PASSWORD_HASH))
    {
      throw new OutOfBoundsException(sprintf(
        'value must be null or between 1-%d characters', self::MAX_PASSWORD_HASH
      ));
    }

    $this->password_hash = $value;
  }

  /**
   * Sets the password salt for this user.
   * Note: Salt is deprecated, this is now always set null for new passwords.
   *
   * @param string|null $value The password salt.
   * @return void
   * @throws OutOfBoundsException if value must be null or between 1-MAX_PASSWORD_SALT characters.
   */
  public function setPasswordSalt(?string $value) : void
  {
    if (!is_null($value) && (empty($value) || strlen($value) > self::MAX_PASSWORD_SALT))
      throw new OutOfBoundsException(sprintf(
        'value must be null or between 1-%d characters', self::MAX_PASSWORD_SALT
      ));

    $this->password_salt = $value;
  }

  /**
   * Sets the Date & Time this user record was last updated.
   * This is implicitly called by allocate() & commit() and is thus unnecessary to call elsewhere.
   *
   * @param DateTimeInterface|string $value The Date & Time interface-compatible value.
   * @return void
   */
  public function setRecordUpdated(DateTimeInterface|string $value) : void
  {
    $this->record_updated = (is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value
    );
  }

  /**
   * Sets the preferred timezone for this user, or null for no preference/automatic.
   *
   * @param string|null $value The timezone.
   * @return void
   * @throws OutOfBoundsException if value must be null or between 1-MAX_TIMEZONE characters.
   * @throws UnexpectedValueException if value must be a valid timezone.
   */
  public function setTimezone(?string $value) : void
  {
    if (!is_null($value) && (empty($value) || strlen($value) > self::MAX_TIMEZONE))
      throw new OutOfBoundsException(sprintf(
        'value must be null or between 1-%d characters', self::MAX_TIMEZONE
      ));

    // Create anonymous DateTimeZone object with $value to test for unknown or bad timezone.
    // PHP throws Exception(sprintf("Unknown or bad timezone (%s)", $value)) if so.
    // This Exception is wrapped into a new UnexpectedValueException.
    try
    {
      if (!empty($value)) new DateTimeZone($value);
    }
    catch (\Exception $e)
    {
      throw new UnexpectedValueException('value must be a valid timezone', 0, $e);
    }

    $this->timezone = $value;
  }

  /**
   * Sets the username for this user.
   *
   * @param string $value The username.
   * @param boolean $ignore_empty Whether an empty value should be accepted, defaults false.
   * @return void
   * @throws OutOfBoundsException if value must be between 1-MAX_USERNAME characters.
   */
  public function setUsername(string $value, bool $ignore_empty = false) : void
  {
    if ((!$ignore_empty && empty($value)) || strlen($value) > self::MAX_USERNAME)
      throw new OutOfBoundsException(sprintf(
        'value must be a string between 1-%d characters', self::MAX_USERNAME
      ));

    $this->username = $value;
  }

  /**
   * Sets the verified status for this user.
   * If the verification changes, the Date & Time and verifier token also will be updated.
   *
   * @param boolean $value Whether this user is verified.
   * @param boolean $reset Whether the Date & Time and verifier token should be reset if value is unchanged.
   * @return void
   */
  public function setVerified(bool $value, bool $reset = false) : void
  {
    $old_value = $this->getOption(self::OPTION_VERIFIED);
    if (!$reset && $old_value === $value) return; // avoid resetting values every call

    $this->setOption(self::OPTION_VERIFIED, $value);

    if ($value)
    {
      // verified
      $this->setVerifiedDateTime(new DateTimeImmutable('now'));
      $this->setVerifierToken(null);
    }
    else
    {
      // not verified
      $this->setVerifiedDateTime(null);
      $this->setVerifierToken(self::generateVerifierToken($this->username ?? '', $this->email ?? ''));
    }
  }

  /**
   * Sets the Date & Time this user record was verified via email address, or null for not verified.
   *
   * @param DateTimeInterface|string|null $value The Date & Time interface-compatible value.
   * @return void
   */
  public function setVerifiedDateTime(DateTimeInterface|string|null $value) : void
  {
    $this->verified_datetime = (is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value
    );
  }

  /**
   * Sets the verifier token for this user, to be used with email address verification.
   *
   * @param string|null $value The verifier token.
   * @return void
   * @throws OutOfBoundsException if value must be null or between 1-MAX_VERIFIER_TOKEN characters.
   */
  public function setVerifierToken(?string $value) : void
  {
    if (!is_null($value) && (empty($value) || strlen($value) > self::MAX_VERIFIER_TOKEN))
      throw new OutOfBoundsException(sprintf(
        'value must be null or between 1-%d characters', self::MAX_VERIFIER_TOKEN
      ));

    $this->verifier_token = $value;
  }
}
