<?php
/**
 *  BNETDocs, the documentation and discussion website for Blizzard protocols
 *  Copyright (C) 2003-2018  "Arta", Don Cullen "Kyro", Carl Bennett, others
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

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\VersionInfo;
use \CarlBennett\MVC\Libraries\Cache;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\GlobalErrorHandler;
use \CarlBennett\MVC\Libraries\Router;

function main() {

  if (!file_exists(__DIR__ . "/../lib/autoload.php")) {
    http_response_code(500);
    exit("Server misconfigured. Please run `composer install`.");
  }
  require(__DIR__ . "/../lib/autoload.php");

  GlobalErrorHandler::createOverrides();

  date_default_timezone_set('Etc/UTC');

  Common::$config = json_decode(file_get_contents(
    __DIR__ . "/../etc/config.phoenix.json"
  ));

  VersionInfo::$version = VersionInfo::get();

  // This must come after GlobalErrorHandler::createOverrides() so that Logger
  // has a chance to create its own error handlers for Application Performance
  // Monitoring (APM) purposes. This must also come after assignment of
  // Common::$config because we may need access tokens from the config.
  Logger::initialize();

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

  Authentication::verify();

  $router = new Router(
    "BNETDocs\\Controllers\\",
    "BNETDocs\\Views\\"
  );

  if (Common::$config->bnetdocs->maintenance[0]) {
    $router->addRoute( // URL: *
      "#.*#", "Maintenance", "MaintenanceHtml",
      Common::$config->bnetdocs->maintenance[1]
    );
  } else {
    $router->addRoute( // URL: /
      "#^/$#", "Legacy", "LegacyHtml"
    );
    $router->addRoute( // URL: /comment/create
      "#^/comment/create/?$#", "Comment\\Create", "Comment\\CreateJSON"
    );
    $router->addRoute( // URL: /comment/delete
      "#^/comment/delete/?$#", "Comment\\Delete", "Comment\\DeleteHtml"
    );
    $router->addRoute( // URL: /credits
      "#^/credits/?$#", "Credits", "CreditsHtml"
    );
    $router->addRoute( // URL: /document/:id.txt
      "#^/document/(\d+)\.txt#", "Document\\View", "Document\\ViewPlain"
    );
    $router->addRoute( // URL: /document/:id
      "#^/document/(\d+)/?#", "Document\\View", "Document\\ViewHtml"
    );
    $router->addRoute( // URL: /document/create
      "#^/document/create/?$#", "Document\\Create", "Document\\CreateHtml"
    );
    $router->addRoute( // URL: /document/delete
      "#^/document/delete/?$#", "Document\\Delete", "Document\\DeleteHtml"
    );
    $router->addRoute( // URL: /document/edit
      "#^/document/edit/?$#", "Document\\Edit", "Document\\EditHtml"
    );
    $router->addRoute( // URL: /document/index
      "#^/document/index/?$#", "Document\\Index", "Document\\IndexHtml"
    );
    $router->addRoute( // URL: /document/index.json
      "#^/document/index\.json/?$#", "Document\\Index", "Document\\IndexJSON"
    );
    $router->addRoute( // URL: /document/popular
      "#^/document/popular/?$#", "Document\\Popular", "Document\\PopularHtml"
    );
    $router->addRoute( // URL: /document/search
      "#^/document/search/?$#", "Document\\Search", "Document\\SearchHtml"
    );
    $router->addRoute( // URL: /donate
      "#^/donate/?$#", "Donate", "DonateHtml"
    );
    $router->addRoute( // URL: /eventlog/index
      "#^/eventlog/index/?$#", "EventLog\\Index", "EventLog\\IndexHtml"
    );
    $router->addRoute( // URL: /eventlog/view
      "#^/eventlog/view/?$#", "EventLog\\View", "EventLog\\ViewHtml"
    );
    $router->addRoute( // URL: /legal
      "#^/legal/?$#", "Legal", "LegalHtml"
    );
    $router->addRoute( // URL: /legal.txt
      "#^/legal.txt$#", "Legal", "LegalPlain"
    );
    $router->addRoute( // URL: /news
      "#^/news/?$#", "News", "NewsHtml"
    );
    $router->addRoute( // URL: /news/:id.txt
      "#^/news/(\d+)\.txt#", "News\\View", "News\\ViewPlain"
    );
    $router->addRoute( // URL: /news/:id
      "#^/news/(\d+)/?#", "News\\View", "News\\ViewHtml"
    );
    $router->addRoute( // URL: /news.rss
      "#^/news\.rss$#", "News", "NewsRSS"
    );
    $router->addRoute( // URL: /news/create
      "#^/news/create/?$#", "News\\Create", "News\\CreateHtml"
    );
    $router->addRoute( // URL: /news/edit
      "#^/news/edit/?$#", "News\\Edit", "News\\EditHtml"
    );
    $router->addRoute( // URL: /news/delete
      "#^/news/delete/?$#", "News\\Delete", "News\\DeleteHtml"
    );
    $router->addRoute( // URL: /packet/:id.txt
      "#^/packet/(\d+)\.txt#", "Packet\\View", "Packet\\ViewPlain"
    );
    $router->addRoute( // URL: /packet/:id
      "#^/packet/(\d+)/?#", "Packet\\View", "Packet\\ViewHtml"
    );
    //$router->addRoute( // URL: /packet/create
    //  "#^/packet/create/?$#", "Packet\\Create", "Packet\\CreateHtml"
    //);
    $router->addRoute( // URL: /packet/edit
      "#^/packet/edit/?$#", "Packet\\Edit", "Packet\\EditHtml"
    );
    $router->addRoute( // URL: /packet/index.cpp
      "#^/packet/index\.cpp/?$#", "Packet\\Index", "Packet\\IndexCpp"
    );
    $router->addRoute( // URL: /packet/index.json
      "#^/packet/index\.json/?$#", "Packet\\Index", "Packet\\IndexJSON"
    );
    $router->addRoute( // URL: /packet/index.java
      "#^/packet/index\.java/?$#", "Packet\\Index", "Packet\\IndexJava"
    );
    $router->addRoute( // URL: /packet/index.php
      "#^/packet/index\.php/?$#", "Packet\\Index", "Packet\\IndexPHP"
    );
    $router->addRoute( // URL: /packet/index.vb
      "#^/packet/index\.vb/?$#", "Packet\\Index", "Packet\\IndexVB"
    );
    $router->addRoute( // URL: /packet/index
      "#^/packet/index/?$#", "Packet\\Index", "Packet\\IndexHtml"
    );
    $router->addRoute( // URL: /packet/popular
      "#^/packet/popular/?$#", "Packet\\Popular", "Packet\\PopularHtml"
    );
    $router->addRoute( // URL: /packet/search
      "#^/packet/search/?$#", "Packet\\Search", "Packet\\SearchHtml"
    );
    $router->addRoute( // URL: /server/:id.json
      "#^/server/(\d+)/?.*\.json$#", "Server\\View", "Server\\ViewJSON"
    );
    $router->addRoute( // URL: /server/:id.txt
      "#^/server/(\d+)/?.*\.txt$#", "Server\\View", "Server\\ViewPlain"
    );
    $router->addRoute( // URL: /server/:id
      "#^/server/(\d+)/?#", "Server\\View", "Server\\ViewHtml"
    );
    //$router->addRoute( // URL: /server/create
    //  "#^/server/create/?$#", "Server\\Create", "Server\\CreateHtml"
    //);
    $router->addRoute( // URL: /servers
      "#^/servers/?$#", "Servers", "ServersHtml"
    );
    $router->addRoute( // URL: /servers.json
      "#^/servers\.json$#", "Servers", "ServersJSON"
    );
    $router->addRoute( // URL: /status
      "#^/status/?$#", "RedirectSoft", "RedirectSoftHtml", "/status.json"
    );
    $router->addRoute( // URL: /status.json
      "#^/status\.json/?$#", "Status", "StatusJSON"
    );
    $router->addRoute( // URL: /status.txt
      "#^/status\.txt/?$#", "Status", "StatusPlain"
    );
    $router->addRoute( // URL: /user/:id
      "#^/user/(\d+)/?#", "User\\View", "User\\ViewHtml"
    );
    $router->addRoute( // URL: /user/changepassword
      "#^/user/changepassword/?$#",
      "User\\ChangePassword", "User\\ChangePasswordHtml"
    );
    $router->addRoute( // URL: /user/createpassword
      "#^/user/createpassword/?$#",
      "User\\CreatePassword", "User\\CreatePasswordHtml"
    );
    $router->addRoute( // URL: /user/index
      "#^/user/index/?$#", "User\\Index", "User\\IndexHtml"
    );
    $router->addRoute( // URL: /user/login
      "#^/user/login/?$#", "User\\Login", "User\\LoginHtml"
    );
    $router->addRoute( // URL: /user/logout
      "#^/user/logout/?$#", "User\\Logout", "User\\LogoutHtml"
    );
    $router->addRoute( // URL: /user/register
      "#^/user/register/?$#", "User\\Register", "User\\RegisterHtml"
    );
    $router->addRoute( // URL: /user/resetpassword
      "#^/user/resetpassword/?$#",
      "User\\ResetPassword", "User\\ResetPasswordHtml"
    );
    $router->addRoute( // URL: /user/update
      "#^/user/update/?$#", "User\\Update", "User\\UpdateHtml"
    );
    $router->addRoute("#.*#", "PageNotFound", "PageNotFoundHtml"); // URL: *
  }

  $router->route();
  $router->send();

}

main();
