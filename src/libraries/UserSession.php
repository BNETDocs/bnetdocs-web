<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Router;
use \InvalidArgumentException;
use \LogicException;
use \Serializable;
use \StdClass;
use \http\Cookie;

class UserSession implements Serializable {

  const COOKIE_NAME = "uid";   // user session cookie name
  const SESSION_TTL = 1209600; // 2 weeks in seconds

  public $user_auth_token;
  public $user_id;

  public function __construct($user_id) {
    $this->user_auth_token = "";
    $this->user_id         = $user_id;
  }

  protected static function decodeUIdCookie($v) {
    if (!$v) return null;

    $c = strpos($v, "-");
    if ($c === false) return false;

    $obj        = new StdClass();
    $obj->uid   = (int) substr($v, 0, $c);
    $obj->token =       substr($v, 1 + $c);

    return $obj;
  }

  public static function load(Router &$router) {
    // Get contents of cookie:
    $uid = self::decodeUIdCookie($router->getRequestCookie(self::COOKIE_NAME));
    if (!$uid) return null; // Cookie contents empty or not set

    // Get session from cache:
    $obj = Common::$cache->get("bnetdocs-usersession-" . $uid->uid);
    if ($obj === false) return null; // No session by that user id
    $obj = unserialize($obj);

    // Check if our token matches their token exactly:
    if ($obj->user_auth_token !== $uid->token) {
      // Invalidate their session in case this is a malicious attempt:
      Common::$cache->set("bnetdocs-usersession-" . $uid->uid, "", 1);
      return null;
    }

    // Give the session back:
    return $obj;
  }

  public function invalidate(Router &$router) {
    if (is_null($this->user_id)) {
      throw new LogicException("Cannot invalidate session on a null user id");
    }
    Common::$cache->set("bnetdocs-usersession-" . $this->user_id, "", 1);
    $router->setResponseCookie(self::COOKIE_NAME, "", 0, true, true);
  }

  public function save(Router &$router) {
    // Generate a new token:
    $this->user_auth_token = hash(
      "sha256", microtime(true) . $this->user_id . mt_rand()
    );

    // Save our token to cache:
    Common::$cache->set(
      "bnetdocs-usersession-" . $this->user_id, // key
      serialize($this),                         // value
      self::SESSION_TTL                         // ttl
    );

    // Tell the browser about the token:
    $router->setResponseCookie(
      self::COOKIE_NAME,                             // name
      $this->user_id . "-" . $this->user_auth_token, // value
      self::SESSION_TTL,                             // ttl
      true,                                          // httpOnly
      true                                           // secure
    );
  }

  public function serialize() {
    $obj                  = new StdClass();
    $obj->user_auth_token = $this->user_auth_token;
    $obj->user_id         = $this->user_id;
    return serialize($obj);
  }

  public function unserialize($data) {
    $obj                   = unserialize($data);
    $this->user_auth_token = $obj->user_auth_token;
    $this->user_id         = $obj->user_id;
  }

}
