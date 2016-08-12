<?php

namespace BNETDocs;

use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\VersionInfo;
use \CarlBennett\MVC\Libraries\Cache;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\GlobalErrorHandler;

function main() {

  if (!file_exists(__DIR__ . "/../vendor/autoload.php")) {
    http_response_code(500);
    exit("Server misconfigured. Please run `composer install`.");
  }
  require(__DIR__ . "/../vendor/autoload.php");

  GlobalErrorHandler::createOverrides();

  Logger::initialize();

  Common::$config = json_decode(file_get_contents(
    __DIR__ . "/../etc/config.phoenix.json"
  ));

  Common::$cache = new Cache(
    Common::$config->memcache->servers,
    Common::$config->memcache->connect_timeout,
    Common::$config->memcache->tcp_nodelay
  );

  Common::$database = null;

  DatabaseDriver::$character_set = Common::$config->mysql->character_set;
  DatabaseDriver::$database_name = Common::$config->mysql->database;
  DatabaseDriver::$password      = Common::$config->mysql->password;
  DatabaseDriver::$servers       = Common::$config->mysql->servers;
  DatabaseDriver::$timeout       = Common::$config->mysql->timeout;
  DatabaseDriver::$username      = Common::$config->mysql->username;

  VersionInfo::$version = VersionInfo::get();

  $router = new Router();
  $router->route();
  $router->send();

}

main();
