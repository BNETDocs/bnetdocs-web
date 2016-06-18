<?php

namespace BNETDocs;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \BNETDocs\Libraries\GlobalErrorHandler;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Router;
use \ReflectionClass;

function main() {

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
  Common::$cache    = new Cache();
  Common::$database = null;
  Common::$version  = Common::getVersionProperties();

  $router = new Router();
  $router->route();
  $router->send();

}

main();
