<?php

namespace BNETDocs\Libraries;

use \StdClass;

class VersionInfo {

  const VERSION_INFO_FILE = '../etc/.rsync-version';

  public static $version;

  /**
   * Block instantiation of this object.
   */
  private function __construct() {}

  public static function get() {
    $versions = new StdClass();

    $versions->bnetdocs = self::getBNETDocsVersion();
    $versions->php      = phpversion();

    return $versions;
  }

  private static function getBNETDocsVersion() {
    if ( !file_exists( self::VERSION_INFO_FILE ) ) {
      return null;
    }

    $buffer = file_get_contents( self::VERSION_INFO_FILE );

    if ( empty( $buffer ) ) {
      return null;
    }

    // The deploy server generates the file with "\n",
    // so don't use PHP_EOL here.
    $buffer = explode( "\n", $buffer );

    return $buffer;
  }

}
