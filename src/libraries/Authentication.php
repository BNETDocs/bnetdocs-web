<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\User;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;

use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \PDO;
use \PDOException;

/**
 * Authentication
 * The class that handles authenticating and verifying a client.
 */
class Authentication {

  const COOKIE_NAME    = 'sid';
  const DATE_SQL       = 'Y-m-d H:i:s';
  const MAX_USER_AGENT = 255;
  const TTL            = 2592000; // 1 month

  /**
   * @var string $key
   */
  private static $key;

  /**
   * @var User $user
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
   *
   * @return bool Indicates if the operation succeeded.
   */
  protected static function discard(string $key) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {
      $stmt = Common::$database->prepare('
        DELETE FROM `user_sessions` WHERE `id` = :id LIMIT 1;
      ');

      $stmt->bindParam(':id', $key, PDO::PARAM_STR);

      $r = $stmt->execute();
      $stmt->closeCursor();

    } catch (PDOException $e) {
      throw new QueryException('Cannot delete user session key', $e);
    } finally {
      return $r;
    }
  }

  /**
   * getFingerprint()
   * Generates a fingerprint that we can use to identify a returning client.
   *
   * @param User $user The User object to be used for fingerprinting.
   *
   * @return array The fingerprint details.
   */
  protected static function getFingerprint(User &$user) {
    $fingerprint = array();

    $fingerprint['ip_address'] = getenv('REMOTE_ADDR');
    $fingerprint['user_id']    = (is_null($user) ? null : $user->getId());
    $fingerprint['user_agent'] = substr(
      getenv('HTTP_USER_AGENT'), 0, self::MAX_USER_AGENT
    );

    return $fingerprint;
  }

  /**
   * getPartialIP()
   * Gets the first /24 or /64 for IPv4 or IPv6 addresses respectively.
   *
   * @return string The partial IP address.
   */
  protected static function getPartialIP(string $ip) {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
      $r = long2ip(ip2long($ip) & 0xFFFFFF00);
    } else if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
      $r = inet_ntop(
        substr(unpack('A16', inet_pton($ip))[1], 0, 8) . str_repeat(chr(0), 8)
      );
    } else {
      throw new InvalidArgumentException('$ip is not a valid IP address');
    }
    return $r;
  }

  /**
   * getUniqueKey()
   * Returns a unique string based on unique user data and other entropy.
   *
   * @param User $user The User object to be used for user data.
   *
   * @return string The unique string.
   */
  protected static function getUniqueKey(User &$user) {
    if (!$user instanceof User) {
      throw new InvalidArgumentException('$user is not instance of User');
    }
    return hash( 'sha1',
      mt_rand() . getenv('REMOTE_ADDR') .
      $user->getId() . $user->getEmail() . $user->getUsername() .
      $user->getPasswordHash() . $user->getPasswordSalt() .
      Common::$config->bnetdocs->user_password_pepper
    );
  }

  /**
   * login()
   * Tells the client's browser to store authentication info.
   * Also sets self::$key and self::$user to derived and given values.
   *
   * @param User &$user The User object.
   *
   * @return bool Indicates if the browser cookie was sent.
   */
  public static function login(User &$user) {
    if (!$user instanceof User) {
      throw new InvalidArgumentException('$user is not instance of User');
    }

    self::$key  = self::getUniqueKey($user);
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
  public static function logout() {
    self::discard(self::$key);

    self::$key  = '';
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
   * @param string $key The secret key, typically from the client.
   *
   * @return array The fingerprint details, or false if not found.
   */
  protected static function lookup(string $key) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $dt_now_str = (new DateTime(
      'now', new DateTimeZone('Etc/UTC')
    ))->format(self::DATE_SQL);

    $fingerprint = false;

    try {
      $stmt = Common::$database->prepare('
        SELECT `user_id`, `ip_address`, `user_agent`
        FROM `user_sessions`
        WHERE `id` = :id AND (
          `expires_datetime` = NULL OR
          :dt < `expires_datetime`
        ) LIMIT 1;
      ');

      $stmt->bindParam(':id', $key, PDO::PARAM_STR);
      $stmt->bindParam(':dt', $dt_now_str, PDO::PARAM_STR);

      $r = $stmt->execute();

      if ($r) {
        $fingerprint = $stmt->fetch(PDO::FETCH_ASSOC);
      }

      $stmt->closeCursor();

    } catch (PDOException $e) {
      throw new QueryException('Cannot lookup user session key', $e);
    } finally {
      return $fingerprint;
    }
  }

  /**
   * store()
   * Stores authentication info server-side for lookup later.
   *
   * @param string $key         The secret key.
   * @param array  $fingerprint The fingerprint details.
   *
   * @return bool Indicates if the operation succeeded.
   */
  protected static function store(string $key, array &$fingerprint) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $tz = new DateTimeZone('Etc/UTC');

    $user_id     = $fingerprint['user_id'];
    $ip_address  = $fingerprint['ip_address'];
    $user_agent  = $fingerprint['user_agent'];
    $created_dt  = new DateTime('now', $tz);
    $created_str = $created_dt->format(self::DATE_SQL);
    $expires_dt = new DateTime(
      '@' . ($created_dt->getTimestamp() + self::TTL), $tz
    );
    $expires_str = $expires_dt->format(self::DATE_SQL);

    $r = false;

    try {
      $stmt = Common::$database->prepare('
        INSERT INTO `user_sessions` (
          `id`, `user_id`, `ip_address`, `user_agent`,
          `created_datetime`, `expires_datetime`
        ) VALUES (
          :id, :user_id, :ip_address, :user_agent,
          :created_dt, :expires_dt
        ) ON DUPLICATE KEY UPDATE
          `ip_address` = :ip_address, `user_agent` = :user_agent
        ;
      ');

      $stmt->bindParam(':id', $key, PDO::PARAM_STR);
      $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
      $stmt->bindParam(':user_agent', $user_agent, PDO::PARAM_STR);
      $stmt->bindParam(':created_dt', $created_str, PDO::PARAM_STR);
      $stmt->bindParam(':expires_dt', $expires_str, PDO::PARAM_STR);

      $r = $stmt->execute();
      $stmt->closeCursor();

    } catch (PDOException $e) {
      throw new QueryException('Cannot store user session key', $e);
    } finally {
      return $r;
    }
  }

  /**
   * verify()
   * Restores user information if verification of identification succeeds.
   *
   * @return bool Indicates if verification succeeded.
   */
  public static function verify() {
    // get client's lookup key
    self::$key = (
      isset($_COOKIE[self::COOKIE_NAME]) ? $_COOKIE[self::COOKIE_NAME] : ''
    );

    // no user yet
    self::$user = null;

    // return if cookie is empty or not set
    if (empty(self::$key)) { return false; }

    // lookup key in our store
    $lookup = self::lookup(self::$key);

    // logout and return if we could not verify their info
    if (!$lookup) {
      self::logout();
      return false;
    }

    // logout and return if their fingerprint ip address does not match
    if (self::getPartialIP($lookup['ip_address'])
      !== self::getPartialIP(getenv('REMOTE_ADDR'))) {
      self::logout();
      return false;
    }

    // logout and return if their fingerprint user agent does not match
    if ($lookup['user_agent'] !== getenv('HTTP_USER_AGENT')) {
      self::logout();
      return false;
    }

    // verified info, let's get the user object
    if (isset($lookup['user_id'])) {
      self::$user = new User($lookup['user_id']);
    }

    // if IP is different, update session
    if ($lookup['ip_address'] !== getenv('REMOTE_ADDR')) {
      self::store($key, self::getFingerprint(self::$user));
    }

    return true;
  }

}
