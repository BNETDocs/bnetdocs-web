<?php

namespace BNETDocs;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Router;
use \CarlBennett\MVC\Libraries\Cache;
use \CarlBennett\MVC\Libraries\GlobalErrorHandler;

function main() {

  if (!file_exists(__DIR__ . "/../vendor/autoload.php")) {
    http_response_code(500);
    exit("Server misconfigured. Please run `composer install`.");
  }
  require(__DIR__ . "/../vendor/autoload.php");

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
