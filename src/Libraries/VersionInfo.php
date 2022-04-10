<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Libraries;

use \StdClass;

class VersionInfo
{
  // Set our own EOL constant, ignore/do not use PHP_EOL,
  // the file is generated externally by production deploy job.
  const EOL = "\n";

  // Default path to version file relative to src/ directory.
  const VERSION_INFO_FILE = '../etc/.rsync-version';

  public static $version;

  /**
   * Block instantiation of this object. This class is completely static.
   */
  private function __construct() {}

  public static function get()
  {
    return [
      'bnetdocs' => self::getBNETDocsVersion(),
      'php' => phpversion(),
    ];
  }

  private static function getBNETDocsVersion()
  {
    if (!file_exists(self::VERSION_INFO_FILE))
    {
      return null;
    }

    $buffer = file_get_contents(self::VERSION_INFO_FILE);

    if (!is_string($buffer) || strlen($buffer) == 0)
    {
      return null;
    }

    return explode(self::EOL, $buffer);
  }
}
