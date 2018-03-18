<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\User;

use \CarlBennett\MVC\Libraries\Common;

use \PDO;
use \PDOException;
use \UnexpectedValueException;

/**
 * Authentication
 * The class that handles authenticating and verifying a client.
 */
class Authentication {

  const CACHE_KEY   = 'bnetdocs-auth-%s';
  const COOKIE_NAME = 'sid';
  const TTL         = 2592000; // 1 month

  public static $key;
  public static $user;

  /**
   * __construct()
   * This class is private so it cannot be instantiated.
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
  protected static function discard( $key ) {
    return Common::$cache->delete( sprintf( self::CACHE_KEY, $key ));
  }

  /**
   * getFingerprint()
   * Generates a fingerprint that we can use to identify a returning client.
   *
   * @param User $user The User object to be used for fingerprinting.
   *
   * @return array The fingerprint details.
   */
  protected static function getFingerprint( User &$user ) {
    $fingerprint = array();

    $fingerprint['ip_address'] = getenv( 'REMOTE_ADDR' );

    $fingerprint['user_id'] = (
      isset( self::$user ) ? self::$user->getId() : null
    );

    return $fingerprint;
  }

  /**
   * getUniqueKey()
   * Returns a unique string based on unique user data and other entropy.
   *
   * @param User $user The User object to be used for user data.
   *
   * @return string The unique string.
   */
  protected static function getUniqueKey( User &$user ) {
    return hash( 'sha1',
      mt_rand() . getenv( 'REMOTE_ADDR' ) .
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
  public static function login( User &$user ) {
    if ( !$user instanceof User ) {
      throw new UnexpectedValueException( '$user is not instance of User' );
    }

    self::$key  = self::getUniqueKey( $user );
    self::$user = $user;

    $fingerprint = self::getFingerprint( $user );
    self::store( self::$key, $fingerprint );

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
    self::discard( self::$key );

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
   * @return string The fingerprint details, or false if not found.
   */
  protected static function lookup( $key ) {
    $fingerprint = Common::$cache->get( sprintf( self::CACHE_KEY, $key ));

    if ( $fingerprint !== false ) {
      $fingerprint = unserialize( $fingerprint );
    }

    return $fingerprint;
  }

  /**
   * store()
   * Stores authentication info server-side for lookup later.
   *
   * @param string $key   The secret key.
   * @param string $value The fingerprint details.
   *
   * @return bool Indicates if the operation succeeded.
   */
  protected static function store( $key, &$fingerprint ) {
    return Common::$cache->set(
      sprintf( self::CACHE_KEY, $key ), serialize( $fingerprint ), self::TTL
    );
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
      isset( $_COOKIE[self::COOKIE_NAME] ) ? $_COOKIE[self::COOKIE_NAME] : ''
    );

    // no user yet
    self::$user = null;

    // return if cookie is empty or not set
    if ( empty( self::$key )) { return false; }

    // lookup key in our store
    $lookup = self::lookup( self::$key );

    // logout and return if we could not verify their info
    if ( !$lookup ) {
      self::logout();
      return false;
    }

    // logout and return if their fingerprint ip address does not match
    if ( $lookup['ip_address'] !== getenv( 'REMOTE_ADDR' )) {
      self::logout();
      return false;
    }

    // verified info, let's get the user object
    if ( isset( $lookup['user_id'] )) {
      self::$user = new User( $lookup['user_id'] );
    }

    return true;
  }

}
