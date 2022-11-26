<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Interfaces\DatabaseObject;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DateTimeImmutable;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \DateTimeZone;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \UnexpectedValueException;

/**
 * Authentication
 * The class that handles authenticating and verifying a client.
 */
class Authentication
{
  public const COOKIE_NAME = 'sid';
  public const MAX_USER_AGENT = 255;
  public const TTL = 2592000; // 1 month

  /**
   * @var string $key The unique identifying token, shared between server and client.
   */
  private static $key;

  /**
   * @var User $user The account which has been authenticated by the client, or null for not authenticated.
   */
  public static $user;

  /**
   * __construct()
   * This class's constructor is private to prevent being instantiated.
   * All functionality of this class is meant to be used as a global state
   * rather than individual auth objects.
   */
  private function __construct() {}

  /**
   * discard()
   * Discards fingerprint server-side so lookup cannot succeed.
   *
   * @param string $key The secret key.
   * @return bool Indicates if the operation succeeded.
   * @throws PDOException if a PDO error occurs.
   */
  protected static function discard(string $key) : bool
  {
    $q = Database::instance()->prepare('DELETE FROM `user_sessions` WHERE `id` = ? LIMIT 1;');
    try { return $q && $q->execute([$key]); }
    finally { if ($q) $q->closeCursor(); }
  }

  /**
   * getFingerprint()
   * Generates a fingerprint that may be used to identify a returning client.
   *
   * @param User $user The User object to be used for fingerprinting.
   * @return array The fingerprint dictionary containing key-value pairs.
   */
  protected static function getFingerprint(User $user) : array
  {
    return [
      'ip_address' => getenv('REMOTE_ADDR'),
      'user_agent' => substr(getenv('HTTP_USER_AGENT'), 0, self::MAX_USER_AGENT),
      'user_id' => $user->getId(),
    ];
  }

  /**
   * getPartialIP()
   * Gets the first /24 or /64 bits from IPv4 or IPv6 addresses respectively.
   *
   * @return string|false The partial IP address.
   * @throws InvalidArgumentException when value is not a valid IP address.
   */
  protected static function getPartialIP(string $value) : string|false
  {
    if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
    {
      return long2ip(ip2long($value) & 0xFFFFFF00);
    }
    else if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
    {
      return inet_ntop(substr(inet_pton($value), 0, 8) . str_repeat(chr(0), 8));
    }
    else
    {
      throw new InvalidArgumentException('value is not a valid IP address');
    }
  }

  /**
   * getUniqueToken()
   * Returns a SHA-256 digest hash of unique entropy.
   *
   * @param User $user The User object to be used for user data.
   * @return string The generated unique data, hexadecimal-formatted (64 bytes).
   */
  protected static function getUniqueToken(User $user) : string
  {
    return hash('sha256', sprintf('%d%s%d%s%s%s%s%s',
      mt_rand(), getenv('REMOTE_ADDR'), $user->getId(), $user->getEmail(),
      $user->getUsername(), $user->getPasswordHash(), $user->getPasswordSalt(),
      Common::$config->bnetdocs->user_password_pepper
    ));
  }

  /**
   * login()
   * Tells the client's browser to store authentication info.
   * Also sets self::$key and self::$user to derived and given values.
   *
   * @param User $user The User object being logged into.
   * @return bool Indicates if the browser cookie was sent.
   */
  public static function login(User $user) : bool
  {
    self::$key = self::getUniqueToken($user);
    self::$user = $user;

    $fingerprint = self::getFingerprint($user);
    self::store(self::$key, $fingerprint);

    // 'domain' is an empty string to only allow this specific http host to
    // authenticate, excluding any subdomains. If we were to specify our
    // current http host, it would also include all subdomains.
    // See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie
    return setcookie(
      self::COOKIE_NAME,  // name
      self::$key,         // value
      time() + self::TTL, // expire
      '/',                // path
      '',                 // domain
      true,               // secure
      true                // httponly
    );
  }

  /**
   * logout()
   * Tells the client's browser to discard authentication info.
   *
   * @return bool Indicates if the browser cookie was sent.
   */
  public static function logout() : bool
  {
    self::discard(self::$key);
    self::$key = '';
    self::$user = null;

    // 'domain' is an empty string to only allow this specific http host to
    // authenticate, excluding any subdomains. If we were to specify our
    // current http host, it would also include all subdomains.
    // See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie
    return setcookie(
      self::COOKIE_NAME, // name
      '',                // value
      time(),            // expire
      '/',               // path
      '',                // domain
      true,              // secure
      true               // httponly
    );
  }

  /**
   * lookup()
   * Retrieves fingerprint based on secret key.
   *
   * @param string $key The unique key, ostensibly from the client,
   *                       hexadecimal-formatted and must be 64 bytes in length.
   * @param bool $throw Whether to throw exceptions or simply return false on error.
   * @return array|null The fingerprint details, or false if not found.
   * @throws UnexpectedValueException if key is not 64 characters in length and/or not a hexadecimal-formatted string.
   */
  protected static function lookup(string $key, bool $throw = true) : ?array
  {
    if (strlen($key) !== 64 || !preg_match('/^(?:(0x|0X)?[a-fA-F0-9]+)$/', $key))
    {
      if ($throw) throw new UnexpectedValueException('key must be exactly 64 characters in length formatted as a hexadecimal string');
      else return false;
    }

    $now = (new DateTimeImmutable('now', new DateTimeZone(DatabaseObject::DATE_TZ)))->format(DatabaseObject::DATE_SQL);

    $q = Database::instance()->prepare(
     'SELECT `ip_address`, `user_agent`, `user_id`
      FROM `user_sessions` WHERE `id` = UNHEX(:id) AND
        (`expires_datetime` = NULL OR `expires_datetime` > :dt)
      LIMIT 1;'
    );
    if (!$q || !$q->execute([':dt' => $now, ':id' => $key])) return null;
    try { return $q->fetch(PDO::FETCH_ASSOC); }
    finally { if ($q) $q->closeCursor(); }
  }

  /**
   * store()
   * Stores authentication info server-side for lookup later.
   *
   * @param string $key The unique key, hexadecimal-formatted and
   *                    must be 64 bytes in length.
   * @param array &$fingerprint The fingerprint details.
   * @return bool Indicates if the operation succeeded.
   * @throws UnexpectedValueException if key is not 64 characters in length and/or not a hexadecimal-formatted string.
   */
  protected static function store(string $key, array &$fingerprint) : bool
  {
    if (strlen($key) !== 64 || !preg_match('/^(?:(0x|0X)?[a-fA-F0-9]+)$/', $key))
      throw new UnexpectedValueException('key must be exactly 64 characters in length formatted as a hexadecimal string');

    $q = Database::instance()->prepare('
      INSERT INTO `user_sessions` (
        `created_datetime`,
        `expires_datetime`,
        `id`,
        `ip_address`,
        `user_agent`,
        `user_id`
      ) VALUES (
        :c, :e, UNHEX(:id), :ip, :ua, :uid
      ) ON DUPLICATE KEY UPDATE
        `created_datetime` = :c,
        `expires_datetime` = :e,
        `id` = UNHEX(:id),
        `ip_address` = :ip,
        `user_agent` = :ua,
        `user_id` = :uid;
    ');

    $tz = new DateTimeZone(DatabaseObject::DATE_TZ);
    $now = new DateTimeImmutable('now', $tz);
    $p = [
      ':c' => $now,
      ':e' => new DateTimeImmutable(sprintf('@%d', $now->getTimestamp() + self::TTL), $tz),
      ':id' => $key,
      ':ip' => $fingerprint['ip_address'],
      ':ua' => $fingerprint['user_agent'],
      ':uid' => $fingerprint['user_id'],
    ];

    foreach ($p as $k => $v)
      if ($v instanceof \DateTimeInterface)
        $p[$k] = $v->format(DatabaseObject::DATE_SQL);

    try { return $q && $q->execute($p); }
    finally { if ($q) $q->closeCursor(); }
  }

  /**
   * verify()
   * Restores user information if verification of identification succeeds.
   *
   * @return bool Indicates if verification succeeded.
   */
  public static function verify() : bool
  {
    // get client's lookup key
    self::$key = $_COOKIE[self::COOKIE_NAME] ?? '';

    // no user yet
    self::$user = null;

    // return if cookie is empty or not set
    if (empty(self::$key)) { return false; }

    // lookup key in our store
    $lookup = self::lookup(self::$key, false);

    // logout and return if we could not verify their info
    if (!$lookup)
    {
      self::logout();
      return false;
    }

    // get remote address
    $ip = getenv('REMOTE_ADDR');

    // logout and return if their fingerprint ip address does not match
    if (self::getPartialIP($lookup['ip_address']) !== self::getPartialIP($ip))
    {
      self::logout();
      return false;
    }

    // logout and return if their fingerprint user agent does not match
    if ($lookup['user_agent'] !== getenv('HTTP_USER_AGENT'))
    {
      self::logout();
      return false;
    }

    // verified info, let's get the user object
    if (isset($lookup['user_id']))
    {
      self::$user = new User($lookup['user_id']);
    }

    // if IP is different, update session
    if ($lookup['ip_address'] !== $ip)
    {
      $new_fingerprint = self::getFingerprint(self::$user);
      self::store(self::$key, $new_fingerprint);
    }

    return true;
  }
}
