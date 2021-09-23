<?php
namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Logger;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\IP;

class BlizzardChecker
{
  const StatusUnknown = 0;
  const StatusNotBlizzard = 1;
  const StatusBlizzard = 2;

  private static int $status = self::StatusUnknown;

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
    self::$status = (
      IP::checkCIDRArray($IP, $CIDRs) ?
      self::StatusBlizzard : self::StatusNotBlizzard
    );
    return (self::$status === self::StatusBlizzard);
  }

  public static function isBlizzard()
  {
    if (self::$status === self::StatusUnknown)
      return self::checkIfBlizzard();
    else
      return (self::$status === self::StatusBlizzard);
  }

  public static function logIfBlizzard()
  {
    $user_id = (
      isset(Authentication::$user) ? Authentication::$user->getId() : null
    );
    if (self::isBlizzard())
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
          'version'    => VersionInfo::$version,
        ])
      );
    }
  }
}
