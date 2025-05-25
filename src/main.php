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

use \BNETDocs\Libraries\Core\Config;
use \BNETDocs\Libraries\Core\Router;

class Main
{
  public const EXIT_FAILURE = 1;
  public const EXIT_SUCCESS = 0;

  public static function main(int $argc, array $argv): int
  {
    if (!file_exists(__DIR__ . '/../lib/autoload.php'))
    {
      \http_response_code(\BNETDocs\Libraries\Core\HttpCode::HTTP_INTERNAL_SERVER_ERROR);
      die('Server misconfigured. Please run `composer install`.');
      return self::EXIT_FAILURE;
    }
    require(__DIR__ . '/../lib/autoload.php');

    \date_default_timezone_set('Etc/UTC');
    \BNETDocs\Libraries\Core\ExceptionHandler::register();
    \BNETDocs\Libraries\User\Authentication::verify();
    \BNETDocs\Libraries\Core\BlizzardCheck::log_blizzard_request();
    \BNETDocs\Libraries\Core\SlackCheck::log_slack_request();

    Router::$route_not_found = ['Core\\NotFound', ['Core\\NotFoundHtml', 'Core\\NotFoundJson', 'Core\\NotFoundPlain']];
    if (Config::get('bnetdocs.maintenance.enabled'))
    {
      Router::$routes = [
        ['#.*#', 'Core\\Maintenance', ['Core\\MaintenanceHtml'], Config::get('bnetdocs.maintenance.message')],
      ];
      Router::invoke();
      return self::EXIT_FAILURE;
    }
    else
    {
      Router::$routes = [
        ['#^/\.well-known/change-password$#', 'Core\\Redirect', ['Core\\RedirectHtml', 'Core\\RedirectJson', 'Core\\RedirectPlain'], '/user/changepassword'],
        ['#^/$#', 'Core\\Legacy', ['Core\\LegacyHtml']],
        ['#^/analytics/dashboard/?$#', 'Analytics\\Dashboard', ['Analytics\\DashboardHtml', 'Analytics\\DashboardJson', 'Analytics\\DashboardPlain']],
        ['#^/analytics/dashboard\.html?$#', 'Analytics\\Dashboard', ['Analytics\\DashboardHtml']],
        ['#^/analytics/dashboard\.json$#', 'Analytics\\Dashboard', ['Analytics\\DashboardJson']],
        ['#^/analytics/dashboard\.txt$#', 'Analytics\\Dashboard', ['Analytics\\DashboardPlain']],
        ['#^/comment/create/?$#', 'Comment\\Create', ['Comment\\CreateJson']],
        ['#^/comment/delete/?$#', 'Comment\\Delete', ['Comment\\DeleteHtml']],
        ['#^/comment/edit/?$#', 'Comment\\Edit', ['Comment\\EditHtml']],
        ['#^/credits/?$#', 'Community\\Credits', ['Community\\CreditsHtml']],
        ['#^/discord/?$#', 'Community\\Discord', ['Community\\DiscordHtml']],
        ['#^/document/(\d+)/?.*\.html?$#', 'Document\\View', ['Document\\ViewHtml']],
        ['#^/document/(\d+)/?.*\.json$#', 'Document\\View', ['Document\\ViewJson']],
        ['#^/document/(\d+)/?.*\.txt$#', 'Document\\View', ['Document\\ViewPlain']],
        ['#^/document/(\d+)/?#', 'Document\\View', ['Document\\ViewHtml', 'Document\\ViewPlain']],
        ['#^/document/create/?$#', 'Document\\Create', ['Document\\CreateHtml']],
        ['#^/document/delete/?$#', 'Document\\Delete', ['Document\\DeleteHtml']],
        ['#^/document/edit/?$#', 'Document\\Edit', ['Document\\EditHtml']],
        ['#^/document/index/?$#', 'Document\\Index', ['Document\\IndexHtml', 'Document\\IndexJson']],
        ['#^/document/index\.html?$#', 'Document\\Index', ['Document\\IndexHtml']],
        ['#^/document/index\.json$#', 'Document\\Index', ['Document\\IndexJson']],
        ['#^/donate/?$#', 'Community\\Donate', ['Community\\DonateHtml']],
        ['#^/eventlog/index/?$#', 'EventLog\\Index', ['EventLog\\IndexHtml']],
        ['#^/eventlog/view/?$#', 'EventLog\\View', ['EventLog\\ViewHtml']],
        ['#^/legal/?$#', 'Community\\Legal', ['Community\\LegalHtml', 'Community\\LegalPlain']],
        ['#^/legal\.html?$#', 'Community\\Legal', ['Community\\LegalHtml']],
        ['#^/legal\.txt$#', 'Community\\Legal', ['Community\\LegalPlain']],
        ['#^/news/?$#', 'News\\Index', ['News\\IndexHtml', 'News\\IndexRSS'], false],
        ['#^/news/(\d+)/?.*\.html?$#', 'News\\View', ['News\\ViewHtml']],
        ['#^/news/(\d+)/?.*\.json$#', 'News\\View', ['News\\ViewJson']],
        ['#^/news/(\d+)/?.*\.txt$#', 'News\\View', ['News\\ViewPlain']],
        ['#^/news/(\d+)/?#', 'News\\View', ['News\\ViewHtml', 'News\\ViewJson', 'News\\ViewPlain']],
        ['#^/news/create/?$#', 'News\\Create', ['News\\CreateHtml']],
        ['#^/news/delete/?$#', 'News\\Delete', ['News\\DeleteHtml']],
        ['#^/news/edit/?$#', 'News\\Edit', ['News\\EditHtml']],
        ['#^/news\.html?$#', 'News\\Index', ['News\\IndexHtml'], false],
        ['#^/news\.rss$#', 'News\\Index', ['News\\IndexRSS'], true],
        ['#^/packet/(\d+)/?.*\.html?$#', 'Packet\\View', ['Packet\\ViewHtml']],
        ['#^/packet/(\d+)/?.*\.json$#', 'Packet\\View', ['Packet\\ViewJson']],
        ['#^/packet/(\d+)/?.*\.txt$#', 'Packet\\View', ['Packet\\ViewPlain']],
        ['#^/packet/(\d+)/?#', 'Packet\\View', ['Packet\\ViewHtml', 'Packet\\ViewJson', 'Packet\\ViewPlain']],
        ['#^/packet/create/?$#', 'Packet\\Create', ['Packet\\CreateHtml']],
        ['#^/packet/delete/?$#', 'Packet\\Delete', ['Packet\\DeleteHtml']],
        ['#^/packet/edit/?$#', 'Packet\\Edit', ['Packet\\EditHtml']],
        ['#^/packet/index/?$#', 'Packet\\Index', ['Packet\\IndexHtml', 'Packet\\IndexJson'], false],
        ['#^/packet/index\.c(?:pp)?$#', 'Packet\\Index', ['Packet\\IndexCpp'], true],
        ['#^/packet/index\.go$#', 'Packet\\Index', ['Packet\\IndexGo'], true],
        ['#^/packet/index\.html?$#', 'Packet\\Index', ['Packet\\IndexHtml'], false],
        ['#^/packet/index\.java$#', 'Packet\\Index', ['Packet\\IndexJava'], true],
        ['#^/packet/index\.json$#', 'Packet\\Index', ['Packet\\IndexJson'], false],
        ['#^/packet/index\.php$#', 'Packet\\Index', ['Packet\\IndexPhp'], true],
        ['#^/packet/index\.vb$#', 'Packet\\Index', ['Packet\\IndexVb'], true],
        ['#^/phpinfo/?$#', 'Core\\PhpInfo', ['Core\\PhpInfoHtml']],
        ['#^/privacy(?:/|\.html?)?$#', 'Community\\PrivacyPolicy', ['Community\\PrivacyPolicyHtml']],
        ['#^/robots\.json$#', 'Core\\Robotstxt', ['Core\\RobotstxtJson']],
        ['#^/robots\.txt$#', 'Core\\Robotstxt', ['Core\\RobotstxtPlain']],
        ['#^/search\.html?$#', 'Search\\Search', ['Search\\SearchHtml']],
        ['#^/search\.json$#', 'Search\\Search', ['Search\\SearchJson']],
        ['#^/search\.txt$#', 'Search\\Search', ['Search\\SearchPlain']],
        ['#^/search$#', 'Search\\Search', ['Search\\SearchHtml', 'Search\\SearchJson', 'Search\\SearchPlain']],
        ['#^/server/(\d+)/?.*\.html?$#', 'Server\\View', ['Server\\ViewHtml']],
        ['#^/server/(\d+)/?.*\.json$#', 'Server\\View', ['Server\\ViewJson']],
        ['#^/server/(\d+)/?.*\.txt$#', 'Server\\View', ['Server\\ViewPlain']],
        ['#^/server/(\d+)/?#', 'Server\\View', ['Server\\ViewHtml', 'Server\\ViewJson', 'Server\\ViewPlain']],
        ['#^/server/create/?$#', 'Server\\Create', ['Server\\CreateHtml']],
        ['#^/server/delete/?$#', 'Server\\Delete', ['Server\\DeleteHtml']],
        ['#^/server/edit/?$#', 'Server\\Edit', ['Server\\EditHtml']],
        ['#^/server/updatejob\.json$#', 'Server\\UpdateJob', ['Server\\UpdateJobJson']],
        ['#^/servers/?$#', 'Server\\Index', ['Server\\IndexHtml', 'Server\\IndexJson']],
        ['#^/servers\.html?$#', 'Server\\Index', ['Server\\IndexHtml']],
        ['#^/servers\.json$#', 'Server\\Index', ['Server\\IndexJson']],
        ['#^/status/?$#', 'Core\\Status', ['Core\\StatusJson', 'Core\\StatusPlain']],
        ['#^/status\.json$#', 'Core\\Status', ['Core\\StatusJson']],
        ['#^/status\.txt$#', 'Core\\Status', ['Core\\StatusPlain']],
        ['#^/user/(\d+)/?.*\.html?$#', 'User\\View', ['User\\ViewHtml']],
        ['#^/user/(\d+)/?.*\.json$#', 'User\\View', ['User\\ViewJson']],
        ['#^/user/(\d+)/?#', 'User\\View', ['User\\ViewHtml', 'User\\ViewJson']],
        ['#^/user/changepassword/?$#', 'User\\ChangePassword', ['User\\ChangePasswordHtml']],
        ['#^/user/createpassword/?$#', 'User\\CreatePassword', ['User\\CreatePasswordHtml']],
        ['#^/user/delete/?#', 'User\\Delete', ['User\\DeleteHtml', 'User\\DeleteJson']],
        ['#^/user/index/?$#', 'User\\Index', ['User\\IndexHtml']],
        ['#^/user/login/?$#', 'User\\Login', ['User\\LoginHtml']],
        ['#^/user/logout/?$#', 'User\\Logout', ['User\\LogoutHtml']],
        ['#^/user/register/?$#', 'User\\Register', ['User\\RegisterHtml']],
        ['#^/user/resetpassword/?$#', 'User\\ResetPassword', ['User\\ResetPasswordHtml']],
        ['#^/user/update/?$#', 'User\\Update', ['User\\UpdateHtml']],
        ['#^/user/verify/?$#', 'User\\Verify', ['User\\VerifyHtml']],
        ['#^/welcome/?$#', 'Community\\Welcome', ['Community\\WelcomeHtml']],
      ];

      Router::invoke();
      return self::EXIT_SUCCESS;
    }
  }
}

exit(Main::main($argc ?? 0, $argv ?? []));
