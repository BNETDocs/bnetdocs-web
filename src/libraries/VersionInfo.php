<?php

namespace BNETDocs\Libraries;

use \StdClass;

class VersionInfo {

  public static $version;

  /**
   * Block instantiation of this object.
   */
  private function __construct() {}

  public static function get() {
    $versions           = new StdClass();
    $versions->bnetdocs = file_get_contents("../etc/.rsync-version");
    $versions->bnetdocs = explode("\n", $versions->bnetdocs);
    $versions->newrelic = phpversion("newrelic");
    $versions->php      = phpversion();
    return $versions;
  }

}
