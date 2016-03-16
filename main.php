<?php

namespace BNETDocs;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \BNETDocs\Libraries\Exceptions\ClassNotFoundException;
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
    $path = str_replace("\\", "/", $path);
    $classShortName = $path;
    $path = getenv("DOCUMENT_ROOT") . "/" . $path . ".php";
    if (!file_exists($path)) {
      throw new ClassNotFoundException($classShortName);
    }
    require_once($path);
  });

  set_exception_handler(function($e){
    while (ob_get_level()) ob_end_clean();
    if ($e instanceof BNETDocsException) {
      http_response_code($e->getHTTPResponseCode());
    } else {
      http_response_code(500);
    }
    header("Cache-Control: max-age=0,must-revalidate,no-cache,no-store");
    header("Content-Type: application/json;charset=utf-8");
    header("Expires: 0");
    header("Pragma: max-age=0");
    if ($e instanceof BNETDocsException) {
      $additional_headers = $e->getHTTPResponseHeaders();
      foreach ($additional_headers as $key => $val) {
        header($key . ": " . $val);
      }
    }
    $flags = (Common::isBrowser(getenv("HTTP_USER_AGENT")) ? JSON_PRETTY_PRINT : 0);
    $json = [
      "error" => [
        "exception" => (new ReflectionClass($e))->getShortName(),
        "code" => $e->getCode(),
        "message" => $e->getMessage(),
      ],
    ];
    if (ini_get("display_errors")) {
      $json["error"]["file"] = Common::stripLeftPattern($e->getFile(), "/home/nginx/bnetdocs-dev");
      $json["error"]["line"] = $e->getLine();
    }
    Logger::logMetric("error_data", json_encode($json, JSON_PRETTY_PRINT));
    Logger::logException($e);
    die(json_encode($json, $flags));
  });

  set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext){
    if (!(error_reporting() & $errno)) return false;
    while (ob_get_level()) ob_end_clean();
    http_response_code(500);
    header("Cache-Control: max-age=0,must-revalidate,no-cache,no-store");
    header("Content-Type: application/json;charset=utf-8");
    header("Expires: 0");
    header("Pragma: max-age=0");
    $flags = (Common::isBrowser(getenv("HTTP_USER_AGENT")) ? JSON_PRETTY_PRINT : 0);
    $json = [
      "error" => [
        "exception" => null,
        "code" => Common::phpErrorName($errno),
      ],
    ];
    if (ini_get("display_errors")) {
      $json["error"]["message"] = $errstr;
      $json["error"]["file"] = Common::stripLeftPattern($errfile, "/home/nginx/bnetdocs-dev");
      $json["error"]["line"] = $errline;
    }
    Logger::logMetric("error_data", json_encode($json, JSON_PRETTY_PRINT));
    Logger::logError($errno, $errstr, $errfile, $errline, $errcontext);
    die(json_encode($json, $flags));
  });

  Logger::initialize();

  Common::$config   = json_decode(file_get_contents("./config.phoenix.json"));
  Common::$cache    = new Cache();
  Common::$database = null;
  Common::$version  = Common::getVersionProperties();

  $router = new Router();
  $router->route();
  $router->send();

}

main();
