<?php
namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Logger;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\IP;

class BlizzardChecker
{
  /**
   * Block instantiation of this object.
   */
  private function __construct() {}

  public static function checkIfBlizzard()
  {
    $IP    = getenv('REMOTE_ADDR');
    $CIDRs = file_get_contents(getcwd() . '/static/a/Blizzard-CIDRs.txt');
    $CIDRs = preg_replace("/^#.*?\n/sm", '', $CIDRs);
    $CIDRs = Common::stripLinesWith($CIDRs, "\n");
    $CIDRs = explode("\n", $CIDRs);
    return IP::checkCIDRArray($IP, $CIDRs);
  }

  public static function logIfBlizzard()
  {
    $user_id = (
      isset(Authentication::$user) ? Authentication::$user->getId() : null
    );
    if (self::checkIfBlizzard())
    {
      Logger::logEvent(
        EventTypes::BLIZZARD_VISIT,
        $user_id,
        getenv('REMOTE_ADDR'),
        json_encode([
          'method'     => getenv('REQUEST_METHOD'),
          'referer'    => getenv('HTTP_REFERER'),
          'uri'        => Common::relativeUrlToAbsolute(getenv('REQUEST_URI')),
          'user_agent' => getenv('HTTP_USER_AGENT'),
          'version'    => VersionInfo::get(),
        ])
      );
    }
  }
}
