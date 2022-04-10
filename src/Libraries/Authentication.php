<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \OutOfBoundsException;
use \PDO;
use \PDOException;
use \UnexpectedValueException;

/**
 * Authentication
 * The class that handles authenticating and verifying a client.
 */
class Authentication
{
  const COOKIE_NAME    = 'sid';
  const DATE_SQL       = 'Y-m-d H:i:s'; // DateTime::format() string for database
  const MAX_USER_AGENT = 0xFF;
  const TTL            = 2592000; // 1 month
  const TZ_SQL         = 'Etc/UTC'; // database values are stored in this TZ

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
  protected static function discard(string $key)
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $q = Common::$database->prepare('DELETE FROM `user_sessions` WHERE `id` = :id LIMIT 1;');
    $q->bindParam(':id', $key, PDO::PARAM_STR);
    return $q->execute();
  }

  /**
   * getFingerprint()
   * Generates a fingerprint that may be used to identify a returning client.
   *
   * @param User $user The User object to be used for fingerprinting.
   * @return array The fingerprint dictionary containing key-value pairs.
   */
  protected static function getFingerprint(User $user)
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
   * @return string The partial IP address.
   * @throws InvalidArgumentException when value is not a valid IP address.
   */
  protected static function getPartialIP(string $value)
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
  protected static function getUniqueToken(User $user)
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
  public static function login(User $user)
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
  public static function logout()
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
   * @return array The fingerprint details, or false if not found.
   * @throws OutOfBoundsException if key is not 64 bytes in length.
   * @throws PDOException if a PDO error occurs.
   * @throws UnexpectedValueException if key is not a hexadecimal-formatted string.
   */
  protected static function lookup(string $key, bool $throw = true)
  {
    if (strlen($key) !== 64)
    {
      if ($throw) throw new OutOfBoundsException('key must be 64 bytes');
      else return false;
    }

    if (!preg_match('/^(?:(0x|0X)?[a-fA-F0-9]+)$/', $key))
    {
      if ($throw) throw new UnexpectedValueException('key must be a hexadecimal-formatted string');
      else return false;
    }

    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $now = (new DateTime('now', new DateTimeZone(self::TZ_SQL)))->format(self::DATE_SQL);

    $q = Common::$database->prepare(
     'SELECT `ip_address`, `user_agent`, `user_id`
      FROM `user_sessions` WHERE `id` = UNHEX(:id) AND
        (`expires_datetime` = NULL OR `expires_datetime` > :dt)
      LIMIT 1;'
    );

    $q->bindParam(':id', $key, PDO::PARAM_STR);
    $q->bindParam(':dt', $now, PDO::PARAM_STR);

    $r = $q->execute();
    if (!$r) return $r;

    return $q->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * store()
   * Stores authentication info server-side for lookup later.
   *
   * @param string $key The unique key, hexadecimal-formatted and
   *                    must be 64 bytes in length.
   * @param array &$fingerprint The fingerprint details.
   * @return bool Indicates if the operation succeeded.
   * @throws OutOfBoundsException if key is not 64 bytes in length.
   * @throws PDOException if a PDO error occurs.
   * @throws UnexpectedValueException if key is not a hexadecimal-formatted string.
   */
  protected static function store(string $key, array &$fingerprint)
  {
    if (strlen($key) !== 64)
    {
      throw new OutOfBoundsException('key must be 64 bytes');
    }

    if (!preg_match('/^(?:(0x|0X)?[a-fA-F0-9]+)$/', $key))
    {
      throw new UnexpectedValueException('key must be a hexadecimal-formatted string');
    }

    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $tz = new DateTimeZone(self::TZ_SQL);

    $ip_address = $fingerprint['ip_address'];
    $user_agent = $fingerprint['user_agent'];
    $user_id = $fingerprint['user_id'];

    $created_dt = new DateTime('now', $tz);
    $created = $created_dt->format(self::DATE_SQL);
    $expires_dt = new DateTime(
      '@' . ($created_dt->getTimestamp() + self::TTL), $tz
    );
    $expires = $expires_dt->format(self::DATE_SQL);

    $q = Common::$database->prepare('
      INSERT INTO `user_sessions` (
        `id`, `user_id`, `ip_address`, `user_agent`,
        `created_datetime`, `expires_datetime`
      ) VALUES (
        UNHEX(:id), :user_id, :ip_address, :user_agent,
        :created, :expires
      ) ON DUPLICATE KEY UPDATE
        `ip_address` = :ip_address, `user_agent` = :user_agent
      ;
    ');

    $q->bindParam(':id', $key, PDO::PARAM_STR);
    $q->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $q->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
    $q->bindParam(':user_agent', $user_agent, PDO::PARAM_STR);
    $q->bindParam(':created', $created, PDO::PARAM_STR);
    $q->bindParam(':expires', $expires, PDO::PARAM_STR);

    return $q->execute();
  }

  /**
   * verify()
   * Restores user information if verification of identification succeeds.
   *
   * @return bool Indicates if verification succeeded.
   */
  public static function verify()
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
