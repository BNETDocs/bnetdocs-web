<?php
/**
 *  BNETDocs, the Battle.net(TM) protocol documentation and discussion website
 *  Copyright (C) 2008-2016  Carl Bennett
 *  This file is part of BNETDocs.
 *
 *  BNETDocs is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  BNETDocs is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with BNETDocs.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace BNETDocs;

use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\VersionInfo;
use \CarlBennett\MVC\Libraries\Cache;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\GlobalErrorHandler;

function main() {

  if (!file_exists(__DIR__ . "/../lib/autoload.php")) {
    http_response_code(500);
    exit("Server misconfigured. Please run `composer install`.");
  }
  require(__DIR__ . "/../lib/autoload.php");

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
