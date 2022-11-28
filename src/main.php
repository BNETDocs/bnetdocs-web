<?php
/**
 *  BNETDocs, the documentation and discussion website for Blizzard protocols
 *  Copyright (C) 2003-2022  "Arta", Don Cullen "Kyro", Carl Bennett, others
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

use \BNETDocs\Libraries\Router;
use \CarlBennett\MVC\Libraries\Common;

function main() : void
{
  if (!file_exists(__DIR__ . '/../lib/autoload.php'))
  {
    \http_response_code(500);
    die('Server misconfigured. Please run `composer install`.');
  }
  require(__DIR__ . '/../lib/autoload.php');

  date_default_timezone_set('Etc/UTC');

  Common::$config = json_decode(file_get_contents(
    __DIR__ . '/../etc/config.phoenix.json'
  ));

  \BNETDocs\Libraries\ExceptionHandler::register();
  \BNETDocs\Libraries\Authentication::verify();
  \BNETDocs\Libraries\BlizzardCheck::log_blizzard_request();
  \BNETDocs\Libraries\SlackCheck::log_slack_request();

  if (Common::$config->bnetdocs->maintenance[0]) {
    Router::$routes = [
      ['#.*#', 'Maintenance', ['MaintenanceHtml'], Common::$config->bnetdocs->maintenance[1]],
    ];
  } else {
    Router::$routes = [
      ['#^/$#', 'Legacy', ['LegacyHtml']],
      ['#^/\.well-known/change-password$#', 'RedirectSoft', ['RedirectSoftHtml', 'RedirectSoftJson', 'RedirectSoftPlain'], '/user/changepassword'],
      ['#^/comment/create/?$#', 'Comment\\Create', ['Comment\\CreateJson']],
      ['#^/comment/delete/?$#', 'Comment\\Delete', ['Comment\\DeleteHtml']],
      ['#^/comment/edit/?$#', 'Comment\\Edit', ['Comment\\EditHtml']],
      ['#^/credits/?$#', 'Credits', ['CreditsHtml']],
      ['#^/discord/?$#', 'Discord', ['DiscordHtml']],
      ['#^/document/(\d+)/?.*\.html?$#', 'Document\\View', ['Document\\ViewHtml']],
      ['#^/document/(\d+)/?.*\.json$#', 'Document\\View', ['Document\\ViewJson']],
      ['#^/document/(\d+)/?.*\.txt$#', 'Document\\View', ['Document\\ViewPlain']],
      ['#^/document/(\d+)/?#', 'Document\\View', ['Document\\ViewHtml', 'Document\\ViewPlain']],
      ['#^/document/create/?$#', 'Document\\Create', ['Document\\CreateHtml']],
      ['#^/document/delete/?$#', 'Document\\Delete', ['Document\\DeleteHtml']],
      ['#^/document/edit/?$#', 'Document\\Edit', ['Document\\EditHtml']],
      ['#^/document/index\.html?$#', 'Document\\Index', ['Document\\IndexHtml']],
      ['#^/document/index\.json$#', 'Document\\Index', ['Document\\IndexJson']],
      ['#^/document/index/?$#', 'Document\\Index', ['Document\\IndexHtml', 'Document\\IndexJson']],
      ['#^/donate/?$#', 'Donate', ['DonateHtml']],
      ['#^/eventlog/index/?$#', 'EventLog\\Index', ['EventLog\\IndexHtml']],
      ['#^/eventlog/view/?$#', 'EventLog\\View', ['EventLog\\ViewHtml']],
      ['#^/legal\.html?$#', 'Legal', ['LegalHtml']],
      ['#^/legal\.txt$#', 'Legal', ['LegalPlain']],
      ['#^/legal/?$#', 'Legal', ['LegalHtml', 'LegalPlain']],
      ['#^/news/?$#', 'News', ['NewsHtml'], false],
      ['#^/news/(\d+)/?.*\.html?$#', 'News\\View', ['News\\ViewHtml']],
      ['#^/news/(\d+)/?.*\.json$#', 'News\\View', ['News\\ViewJson']],
      ['#^/news/(\d+)/?.*\.txt$#', 'News\\View', ['News\\ViewPlain']],
      ['#^/news/(\d+)/?#', 'News\\View', ['News\\ViewHtml', 'News\\ViewJson', 'News\\ViewPlain']],
      ['#^/news\.rss$#', 'News', ['NewsRSS'], true],
      ['#^/news/create/?$#', 'News\\Create', ['News\\CreateHtml']],
      ['#^/news/edit/?$#', 'News\\Edit', ['News\\EditHtml']],
      ['#^/news/delete/?$#', 'News\\Delete', ['News\\DeleteHtml']],
      ['#^/packet/(\d+)/?.*\.html?$#', 'Packet\\View', ['Packet\\ViewHtml']],
      ['#^/packet/(\d+)/?.*\.json$#', 'Packet\\View', ['Packet\\ViewJson']],
      ['#^/packet/(\d+)/?.*\.txt$#', 'Packet\\View', ['Packet\\ViewPlain']],
      ['#^/packet/(\d+)/?#', 'Packet\\View', ['Packet\\ViewHtml', 'Packet\\ViewJson', 'Packet\\ViewPlain']],
      ['#^/packet/create/?$#', 'Packet\\Create', ['Packet\\CreateHtml']],
      ['#^/packet/delete/?$#', 'Packet\\Delete', ['Packet\\DeleteHtml']],
      ['#^/packet/edit/?$#', 'Packet\\Edit', ['Packet\\EditHtml']],
      ['#^/packet/index\.c(?:pp)?$#', 'Packet\\Index', ['Packet\\IndexCpp'], true],
      ['#^/packet/index\.html?$#', 'Packet\\Index', ['Packet\\IndexHtml'], false],
      ['#^/packet/index\.json$#', 'Packet\\Index', ['Packet\\IndexJson'], false],
      ['#^/packet/index\.java$#', 'Packet\\Index', ['Packet\\IndexJava'], true],
      ['#^/packet/index\.php$#', 'Packet\\Index', ['Packet\\IndexPhp'], true],
      ['#^/packet/index\.vb$#', 'Packet\\Index', ['Packet\\IndexVb'], true],
      ['#^/packet/index/?$#', 'Packet\\Index', ['Packet\\IndexHtml', 'Packet\\IndexJson'], false],
      ['#^/phpinfo/?$#', 'PhpInfo', ['PhpInfoHtml']],
      ['#^/privacy(?:/|\.html?)?$#', 'PrivacyNotice', ['PrivacyNoticeHtml']],
      ['#^/robots\.txt$#', 'Robotstxt', ['Robotstxt']],
      ['#^/server/(\d+)/?.*\.html?$#', 'Server\\View', ['Server\\ViewHtml']],
      ['#^/server/(\d+)/?.*\.json$#', 'Server\\View', ['Server\\ViewJson']],
      ['#^/server/(\d+)/?.*\.txt$#', 'Server\\View', ['Server\\ViewPlain']],
      ['#^/server/(\d+)/?#', 'Server\\View', ['Server\\ViewHtml', 'Server\\ViewJson', 'Server\\ViewPlain']],
      ['#^/server/create/?$#', 'Server\\Create', ['Server\\CreateHtml']],
      ['#^/server/delete/?$#', 'Server\\Delete', ['Server\\DeleteHtml']],
      ['#^/server/edit/?$#', 'Server\\Edit', ['Server\\EditHtml']],
      ['#^/server/updatejob\.json$#', 'Server\\UpdateJob', ['Server\\UpdateJobJson']],
      ['#^/servers\.html?$#', 'Servers', ['ServersHtml']],
      ['#^/servers\.json$#', 'Servers', ['ServersJson']],
      ['#^/servers/?$#', 'Servers', ['ServersHtml', 'ServersJson']],
      ['#^/status\.json$#', 'Status', ['StatusJson']],
      ['#^/status\.txt$#', 'Status', ['StatusPlain']],
      ['#^/status/?$#', 'Status', ['StatusJson', 'StatusPlain']],
      ['#^/user/(\d+)/?.*\.html?$#', 'User\\View', ['User\\ViewHtml']],
      ['#^/user/(\d+)/?.*\.json$#', 'User\\View', ['User\\ViewJson']],
      ['#^/user/(\d+)/?#', 'User\\View', ['User\\ViewHtml', 'User\\ViewJson']],
      ['#^/user/changepassword/?$#', 'User\\ChangePassword', ['User\\ChangePasswordHtml']],
      ['#^/user/createpassword/?$#', 'User\\CreatePassword', ['User\\CreatePasswordHtml']],
      ['#^/user/index/?$#', 'User\\Index', ['User\\IndexHtml']],
      ['#^/user/login/?$#', 'User\\Login', ['User\\LoginHtml']],
      ['#^/user/logout/?$#', 'User\\Logout', ['User\\LogoutHtml']],
      ['#^/user/register/?$#', 'User\\Register', ['User\\RegisterHtml']],
      ['#^/user/resetpassword/?$#', 'User\\ResetPassword', ['User\\ResetPasswordHtml']],
      ['#^/user/update/?$#', 'User\\Update', ['User\\UpdateHtml']],
      ['#^/user/verify/?$#', 'User\\Verify', ['User\\VerifyHtml']],
      ['#^/welcome/?$#', 'Welcome', ['WelcomeHtml']],
    ];

    Router::$route_not_found = ['PageNotFound', ['PageNotFoundHtml', 'PageNotFoundJson', 'PageNotFoundPlain']];
  }

  Router::invoke();
}

main();
