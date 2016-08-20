<?php

namespace BNETDocs\Libraries;

use \CarlBennett\MVC\Libraries\Common as CommonMVCLib;
use \CarlBennett\MVC\Libraries\IP;

class BlizzardChecker {

  /**
   * Block instantiation of this object.
   */
  private function __construct() {}

  public static function checkIfBlizzard() {
    $IP    = getenv("REMOTE_ADDR");
    $CIDRs = file_get_contents(getcwd() . "/static/a/Blizzard-CIDRs.txt");
    $CIDRs = preg_replace("/^#.*?\n/sm", "", $CIDRs);
    $CIDRs = CommonMVCLib::stripLinesWith($CIDRs, "\n");
    $CIDRs = explode("\n", $CIDRs);
    return IP::checkCIDRArray($IP, $CIDRs);
  }

}
