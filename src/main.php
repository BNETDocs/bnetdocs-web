<?php

namespace BNETDocs;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Router;
use \CarlBennett\MVC\Libraries\Cache;
use \CarlBennett\MVC\Libraries\GlobalErrorHandler;
use \ReflectionClass;

function main() {

  if (!file_exists(__DIR__ . "/../vendor/autoload.php")) {
    http_response_code(500);
    exit("Server misconfigured. Please run `composer install`.");
  }
  require(__DIR__ . "/../vendor/autoload.php");

  spl_autoload_register(function($className){
    $path = $className;
    if (substr($path, 0, 8) == "BNETDocs") $path = substr($path, 9);
    $cursor = strpos($path, "\\");
    if ($cursor !== false) {
      $path = strtolower(substr($path, 0, $cursor)) . substr($path, $cursor);
    }
    $path = str_replace("\\", DIRECTORY_SEPARATOR, $path);
    $classShortName = $path;
    $docroot = getenv("DOCUMENT_ROOT");
    if (empty($docroot)) $docroot = ".";
    $path = $docroot . DIRECTORY_SEPARATOR . $path . ".php";
    if (!file_exists($path)) {
      trigger_error("Class not found: " . $classShortName, E_USER_ERROR);
    }
    require_once($path);
  });

  GlobalErrorHandler::createOverrides();

  Logger::initialize();

  Common::$config   = json_decode(file_get_contents(
                        "../etc/config.phoenix.json"
                      ));
  Common::$cache    = new Cache(
                        Common::$config->memcache->servers,
                        Common::$config->memcache->connect_timeout,
                        Common::$config->memcache->tcp_nodelay
                      );
  Common::$database = null;
  Common::$version  = Common::getVersionProperties();

  $router = new Router();
  $router->route();
  $router->send();

}

main();
