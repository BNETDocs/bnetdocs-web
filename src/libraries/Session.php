<?php

namespace BNETDocs\Libraries;

use CarlBennett\MVC\Libraries\Router;
use CarlBennett\MVC\Libraries\Session as BaseSession;

class Session extends BaseSession  {

  const COOKIE_NAME = 'uid';
  const LOGIN_TTL   = 2592000; // 1 month

  public static function checkLogin(Router &$router) {
    // Check if logged in by session but long-term cookie not set
    if (isset($_SESSION['user_id']) && !isset($_COOKIE[self::COOKIE_NAME])) {
      $router->setResponseCookie(
        self::COOKIE_NAME,    // key
        $_SESSION['user_id'], // value
        self::LOGIN_TTL,      // ttl
        true,                 // http only
        true,                 // secure
        getenv('HTTP_HOST'),  // domain
        '/'                   // path
      );
    }
    // Check for login manipulation
    if (isset($_SESSION['user_id']) && isset($_COOKIE[self::COOKIE_NAME])
      && $_SESSION['user_id'] != $_COOKIE[self::COOKIE_NAME]) {
      unset($_SESSION['user_id']);
      $router->setResponseCookie(
        self::COOKIE_NAME, '', 0, true, true, // delete it
        getenv('HTTP_HOST'), '/'
      );
    }
  }

}
