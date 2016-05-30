<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Controllers\Credits as CreditsController;
use \BNETDocs\Controllers\Document\Index as DocumentIndexController;
use \BNETDocs\Controllers\Document\Popular as DocumentPopularController;
use \BNETDocs\Controllers\Document\Search as DocumentSearchController;
use \BNETDocs\Controllers\Document\View as DocumentViewController;
use \BNETDocs\Controllers\Legal as LegalController;
use \BNETDocs\Controllers\Maintenance as MaintenanceController;
use \BNETDocs\Controllers\News as NewsController;
use \BNETDocs\Controllers\News\Create as NewsCreateController;
use \BNETDocs\Controllers\News\Delete as NewsDeleteController;
use \BNETDocs\Controllers\News\Edit as NewsEditController;
use \BNETDocs\Controllers\News\View as NewsViewController;
use \BNETDocs\Controllers\Packet\Index as PacketIndexController;
use \BNETDocs\Controllers\Packet\Popular as PacketPopularController;
use \BNETDocs\Controllers\Packet\Search as PacketSearchController;
use \BNETDocs\Controllers\Packet\View as PacketViewController;
use \BNETDocs\Controllers\PageNotFound as PageNotFoundController;
use \BNETDocs\Controllers\Redirect as RedirectController;
use \BNETDocs\Controllers\Servers as ServersController;
use \BNETDocs\Controllers\Status as StatusController;
use \BNETDocs\Controllers\User\ChangePassword as UserChangePasswordController;
use \BNETDocs\Controllers\User\Login as UserLoginController;
use \BNETDocs\Controllers\User\Logout as UserLogoutController;
use \BNETDocs\Controllers\User\Register as UserRegisterController;
use \BNETDocs\Controllers\User\ResetPassword as UserResetPasswordController;
use \BNETDocs\Controllers\User\View as UserViewController;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\ControllerNotFoundException;
use \BNETDocs\Libraries\UserSession;
use \DateTime;
use \DateTimeZone;
use \SplObjectStorage;
use \UnexpectedValueException;
use \http\Cookie;

class Router {

  protected $hostname;
  protected $requestMethod;
  protected $requestURI;
  protected $pathArray;
  protected $pathString;
  protected $queryArray;
  protected $queryString;
  protected $requestCookies;
  protected $requestBodyArray;
  protected $requestBodyString;
  protected $requestBodyMimeType;

  protected $responseCode;
  protected $responseHeaders;
  protected $responseCookies;
  protected $responseContent;

  public function __construct() {
    $this->hostname = getenv("HTTP_HOST");
    if (empty($this->hostname)) $this->hostname = getenv("SERVER_NAME");
    $this->requestMethod = getenv("REQUEST_METHOD");
    $this->requestURI = getenv("REQUEST_URI");
    $cursor = strpos($this->requestURI, "?");
    if ($cursor !== false) {
      $this->pathString = substr($this->requestURI, 0, $cursor);
      $this->queryString = substr($this->requestURI, $cursor + 1);
    } else {
      $this->pathString = $this->requestURI;
      $this->queryString = "";
    }
    $this->pathArray = explode("/", $this->pathString);
    parse_str($this->queryString, $this->queryArray);
    $this->requestCookies = new Cookie(getenv("HTTP_COOKIE"));
    $this->requestBodyMimeType = getenv("CONTENT_TYPE");
    $this->requestBodyString = $this->_getRequestBodyString();
    $this->requestBodyArray = $this->_getRequestBodyArray();
    $this->responseCode = 500;
    $this->responseHeaders = new SplObjectStorage();
    $this->responseCookies = new SplObjectStorage();
    $this->responseContent = "";
  }

  private function _getRequestBodyString() {
    $len = getenv("CONTENT_LENGTH");
    $buffer = "";
    if ($len === false) {
      $stdin = fopen("php://input", "rb");
      $buffer = stream_get_contents($stdin);
      fclose($stdin);
    } else {
      $len = (int) $len;
      $i = 0;
      $chunk_size = 8192; // default is 8192 according to PHP documentation
      $stdin = fopen("php://input", "r");
      while (!feof($stdin) && $i < $len) {
        $buffer .= fread($stdin, $chunk_size);
      }
      fclose($stdin);
    }
    return $buffer;
  }

  private function _getRequestBodyArray() {
    if (stripos($this->requestBodyMimeType, "application/json") !== false
        || stripos($this->requestBodyMimeType, "text/json") !== false) {
      return json_decode($this->requestBodyString);
    } else if (stripos($this->requestBodyMimeType,
        "application/x-www-form-urlencoded") !== false) {
      $buffer;
      parse_str($this->requestBodyString, $buffer);
      return $buffer;
    } else {
      return null;
    }
  }

  public function addResponseContent($buffer) {
    $this->responseContent .= $buffer;
  }

  public function getHostname() {
    return $this->hostname;
  }

  public function getRequestCookie($name) {
    return $this->requestCookies->getCookie($name);
  }

  public function getRequestCookies() {
    return $this->requestCookies;
  }

  public function getRequestHeader($name) {
    return getenv("HTTP_" . str_replace("-", "_", strtoupper($name)));
  }

  public function getRequestMethod() {
    return $this->requestMethod;
  }

  public function getRequestPathArray() {
    return $this->pathArray;
  }

  public function getRequestPathExtension() {
    return pathinfo($this->pathString, PATHINFO_EXTENSION);
  }

  public function getRequestPathString($with_extension = true) {
    if ($with_extension || strpos($this->pathString, ".") === false) {
      return $this->pathString;
    } else {
      return substr($this->pathString, 0, strrpos($this->pathString, "."));
    }
  }

  public function getRequestBodyArray() {
    return $this->requestBodyArray;
  }

  public function getRequestBodyString() {
    return $this->requestBodyString;
  }

  public function getRequestQueryArray() {
    return $this->queryArray;
  }

  public function getRequestQueryString() {
    return $this->queryString;
  }

  public function getRequestURI() {
    return $this->requestURI;
  }

  public function route(Pair &$redirect = null) {
    $pathArray = $this->getRequestPathArray();
    $path      = (isset($pathArray[1]) ? $pathArray[1] : null);
    $subpath   = (isset($pathArray[2]) ? $pathArray[2] : null);
    $fullpath  = $path . (!empty($subpath) ? "/" . $subpath : "");
    Logger::setTransactionName(
      $fullpath ? $fullpath : "main()"
    );

    if (Common::checkIfBlizzard()) {
      $user_session = UserSession::load($this);
      Logger::logMetric("is_blizzard_visit", true);
      Logger::logEvent(
        "blizzard_visit",
        ($user_session ? $user_session->user_id : null),
        getenv("REMOTE_ADDR"),
        json_encode([
          "path" => $this->getRequestPathString(true),
          "referer" => $this->getRequestHeader("Referer"),
          "user_agent" => $this->getRequestHeader("User-Agent"),
        ])
      );
    } else {
      Logger::logMetric("is_blizzard_visit", false);
    }

    ob_start();

    if (Common::$config->bnetdocs->maintenance[0]) {
      $controller = new MaintenanceController(
        Common::$config->bnetdocs->maintenance[1]
      );
    } else if (isset($redirect)) {
      $controller = new RedirectController(
        $redirect->getKey(), $redirect->getValue()
      );
    } else {
      try {
        switch ($path) {
          case "":
            // Try to route legacy BNETDocs Redux paths to Phoenix paths with a
            // 301 Permanent redirect, otherwise go to the news with 302 Found.
            $query = $this->getRequestQueryArray();
            $did  = (isset($query["did" ]) ? $query["did" ] : null);
            $lang = (isset($query["lang"]) ? $query["lang"] : null);
            $nid  = (isset($query["nid" ]) ? $query["nid" ] : null);
            $op   = (isset($query["op"  ]) ? $query["op"  ] : null);
            $pid  = (isset($query["pid" ]) ? $query["pid" ] : null);
            $url  = null; $code = 301;
            if ($op == "cpw") {
              $url = Common::relativeUrlToAbsolute("/user/changepassword");
            } else if ($op == "credits") {
              $url = Common::relativeUrlToAbsolute("/credits");
            } else if ($op == "doc" && !is_null($did)) {
              $url = Common::relativeUrlToAbsolute("/document/" . rawurlencode($did));
            } else if ($op == "generatecode" && !is_null($lang)) {
              $url = Common::relativeUrlToAbsolute("/packet/index." . rawurlencode($lang));
            } else if ($op == "legalism") {
              $url = Common::relativeUrlToAbsolute("/legal");
            } else if ($op == "login") {
              $url = Common::relativeUrlToAbsolute("/user/login");
            } else if ($op == "news" && !is_null($nid)) {
              $url = Common::relativeUrlToAbsolute("/news/" . rawurlencode($nid));
            } else if ($op == "news") {
              $url = Common::relativeUrlToAbsolute("/news");
            } else if ($op == "packet" && !is_null($pid)) {
              $url = Common::relativeUrlToAbsolute("/packet/" . rawurlencode($pid));
            } else if ($op == "register") {
              $url = Common::relativeUrlToAbsolute("/user/register");
            } else if ($op == "resetpw") {
              $url = Common::relativeUrlToAbsolute("/user/resetpassword");
            }
            if (is_null($url)) {
              $url = Common::relativeUrlToAbsolute("/news");
              $code = 302;
            }
            $controller = new RedirectController($url, $code);
          break;
          case "credits": case "credits.htm": case "credits.html":
            $controller = new CreditsController();
          break;
          case "document":
            switch ($subpath) {
              case "index": case "index.htm": case "index.html":
              case "index.json":
                $controller = new DocumentIndexController();
              break;
              case "popular": case "popular.htm": case "popular.html":
                $controller = new DocumentPopularController();
              break;
              case "search": case "search.htm": case "search.html":
                $controller = new DocumentSearchController();
              break;
              default:
                if (is_numeric($subpath)) {
                  $controller = new DocumentViewController($subpath);
                } else {
                  throw new ControllerNotFoundException(
                    $path . "/" . $subpath
                  );
                }
            }
          break;
          case "legal": case "legal.htm": case "legal.html": case "legal.txt":
            $controller = new LegalController();
          break;
          case "news.htm": case "news.html": case "news.rss":
            $controller = new NewsController();
          break;
          case "news":
            switch ($subpath) {
              case "create": case "create.htm": case "create.html":
                $controller = new NewsCreateController();
              break;
              case "edit": case "edit.htm": case "edit.html":
                $controller = new NewsEditController();
              break;
              case "delete": case "delete.htm": case "delete.html":
                $controller = new NewsDeleteController();
              break;
              default:
                if (is_numeric($subpath)) {
                  $controller = new NewsViewController($subpath);
                } else if (empty($subpath)) {
                  $controller = new NewsController();
                } else {
                  throw new ControllerNotFoundException(
                    $path . "/" . $subpath
                  );
                }
            }
          break;
          case "newsrss.php":
            // Legacy BNETDocs Redux to BNETDocs Phoenix redirect.
            $controller = new RedirectController(
              Common::relativeUrlToAbsolute("/news.rss"), 301
            );
          break;
          case "packet":
            switch ($subpath) {
              case "index": case "index.cpp": case "index.htm": 
              case "index.html": case "index.java": case "index.json":
              case "index.php": case "index.vb":
                $controller = new PacketIndexController();
              break;
              case "popular": case "popular.htm": case "popular.html":
                $controller = new PacketPopularController();
              break;
              case "search": case "search.htm": case "search.html":
                $controller = new PacketSearchController();
              break;
              default:
                if (is_numeric($subpath)) {
                  $controller = new PacketViewController($subpath);
                } else {
                  throw new ControllerNotFoundException(
                    $path . "/" . $subpath
                  );
                }
            }
          break;
          case "rss":
            // Old-style Phoenix to new-style Phoenix redirect.
            switch ($subpath) {
              case "news":
                $controller = new RedirectController(
                  Common::relativeUrlToAbsolute("/news.rss"), 301
                );
              break;
              default:
                throw new ControllerNotFoundException($path . "/" . $subpath);
            }
          break;
          case "servers": case "servers.htm": case "servers.html":
          case "servers.json":
            $controller = new ServersController();
          break;
          case "status": case "status.json": case "status.txt":
            $controller = new StatusController();
          break;
          case "user":
            switch ($subpath) {
              case "changepassword": case "changepassword.htm":
              case "changepassword.html":
                $controller = new UserChangePasswordController();
              break;
              case "login": case "login.htm": case "login.html":
                $controller = new UserLoginController();
              break;
              case "logout": case "logout.htm": case "logout.html":
                $controller = new UserLogoutController();
              break;
              case "register": case "register.htm": case "register.html":
                $controller = new UserRegisterController();
              break;
              case "resetpassword": case "resetpassword.htm":
              case "resetpassword.html":
                $controller = new UserResetPasswordController();
              break;
              default:
                if (is_numeric($subpath)) {
                  $controller = new UserViewController($subpath);
                } else {
                  throw new ControllerNotFoundException(
                    $path . "/" . $subpath
                  );
                }
            }
          break;
          default:
            throw new ControllerNotFoundException($path);
        }
      } catch (ControllerNotFoundException $e) {
        $controller = new PageNotFoundController();
      }
    }

    // Prevent clickjacking globally:
    $this->setResponseHeader("X-Frame-Options", "DENY");

    $controller->run($this);
    $this->addResponseContent(ob_get_contents());

    ob_end_clean();
  }

  public function send() {
    http_response_code($this->responseCode);
    foreach ($this->responseHeaders as $header) {
      header($header->getName() . ": " . $header->getValue());
    }
    foreach ($this->responseCookies as $cookie) {
      header("Set-Cookie: " . $cookie->__toString());
    }
    echo $this->responseContent;
  }

  public function setResponseCode($code) {
    $this->responseCode = $code;
  }

  public function setResponseContent($buffer) {
    $this->responseContent = $buffer;
  }

  public function setResponseCookie($name, $value, $ttl, $httpOnly, $secure) {
    $flags = 0;
    if ($httpOnly) $flags |= Cookie::HTTPONLY;
    if ($secure)   $flags |= Cookie::SECURE;

    $cookie = new Cookie();
    $cookie->setCookie($name, $value);
    $cookie->setDomain(".bnetdocs.org");
    $cookie->setFlags($flags);
    $cookie->setMaxAge($ttl);
    $cookie->setPath("/");

    $this->responseCookies->attach($cookie);
  }

  public function setResponseHeader($arg1, $arg2 = null) {
    if ($arg1 instanceof HTTPHeader) {
      $this->responseHeaders->attach($arg1);
    } else if (is_string($arg1) && is_string($arg2)) {
      $this->responseHeaders->attach(new HTTPHeader($arg1, $arg2));
    } else {
      throw new UnexpectedValueException(
        "Arguments given must be two strings or an HTTPHeader object", -1
      );
    }
  }

  public function setResponseTTL($ttl) {
    $ttl = (int) $ttl;
    if ($ttl < 0) {
      throw new UnexpectedValueException(
        "Argument must be equal to or greater than zero", -1
      );
    }
    $dtz = new DateTimeZone("GMT");
    if ($ttl > 0) {
      $expires = new DateTime("+" . $ttl . " second");
    } else {
      $expires = new DateTime("@0");
    }
    $expires->setTimezone($dtz);
    $this->setResponseHeader("Cache-Control", "max-age=" . $ttl);
    $this->setResponseHeader("Expires", $expires->format("D, d M Y H:i:s e"));
    $this->setResponseHeader("Pragma", "max-age=" . $ttl);
  }

}
