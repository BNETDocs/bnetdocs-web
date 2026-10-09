<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */

namespace BNETDocs\Libraries\Core;

class VersionInfo
{
  // Set our own EOL constant, ignore/do not use PHP_EOL,
  // the file is generated externally by production deploy job.
  public const EOL = "\n";

  // Project root directory, which contains .git/ when running from a git checkout.
  const PROJECT_ROOT = __DIR__ . '/../../..';

  // Default path to version file relative to src/ directory.
  const VERSION_INFO_FILE = self::PROJECT_ROOT . '/etc/deploy-version.txt';

  // Year BNETDocs was founded, the start of the copyright range.
  public const COPYRIGHT_START_YEAR = 2003;

  private static ?array $version = null;

  /**
   * Block instantiation of this object. This class is completely static.
   */
  private function __construct() {}

  /**
   * Gets the version information array for use externally outside this class.
   *
   * @return array The version information.
   */
  public static function get(): array
  {
    if (!is_null(self::$version)) return self::$version;
    $r = [];

    // A git checkout may contain a stale version file left over from a previous
    // deployment, so it is only trusted when there is no git metadata to ask.
    $r['bnetdocs'] = file_exists(self::PROJECT_ROOT . '/.git')
      ? self::reflectVersion() : self::deploymentVersion();
    $r['asset'] = self::assetVersion($r['bnetdocs']);
    $r['php'] = phpversion();

    ksort($r);
    self::$version = $r;
    return $r;
  }

  /**
   * Gets the asset tag string to append to asset URLs.
   * Useful for CDNs to key on when re-deploying cached assets.
   *
   * @param array|null $version The bnetdocs version array.
   * @return string The asset tag value.
   */
  private static function assetVersion(?array $version): string
  {
    return (
      !\BNETDocs\Libraries\Core\Config::get('bnetdocs.asset_versioning') ? '' :
      (!is_null($version) ? $version[1] : date('YmdHis'))
    );
  }

  /**
   * Gets the year of the last commit, for use in copyright notices.
   * Falls back to the current year if the commit timestamp is not available.
   *
   * @return string The four-digit year.
   */
  public static function copyrightYear(): string
  {
    $timestamp = self::get()['bnetdocs'][2] ?? null;
    return self::isTimestamp($timestamp) ? substr($timestamp, 0, 4) : date('Y');
  }

  /**
   * Gets the year range for use in copyright notices, e.g. "2003-2026".
   *
   * @return string The start year and the year of the last commit.
   */
  public static function copyrightYears(): string
  {
    return self::COPYRIGHT_START_YEAR . '-' . self::copyrightYear();
  }

  /**
   * Gets the version information array built by deployment automation:
   * 1. git tag/version identifier string
   * 2. git hash of last commit
   * 3. ISO8601 timestamp of last commit
   * 4. git hash and ISO8601 timestamp of the LICENSE.txt file
   *
   * @return array|null The information array, or null if not available.
   */
  private static function deploymentVersion(): ?array
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

    $values = explode(self::EOL, $buffer);

    // Reject a malformed file rather than display garbage from it.
    if (count($values) < 3
      || !preg_match('/^[0-9a-f]{40}$/', $values[1])
      || !self::isTimestamp($values[2]))
    {
      return null;
    }

    return $values;
  }

  /**
   * Checks whether a value is an ISO8601 timestamp as written by `git log --pretty='%aI'`.
   *
   * @param mixed $value The value to check.
   * @return bool Whether the value is a valid timestamp.
   */
  private static function isTimestamp(mixed $value): bool
  {
    return is_string($value)
      && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(Z|[+-]\d{2}:\d{2})$/', $value) === 1;
  }

  /**
   * Gets the version information array by using git shell commands:
   * 1. git tag/version identifier string
   * 2. git hash of last commit
   * 3. ISO8601 timestamp of last commit
   * 4. git hash and ISO8601 timestamp of the LICENSE.txt file
   *
   * @return array|null The information array, or null if not available.
   */
  private static function reflectVersion(): ?array
  {
    $values = [null, null, null, null];

    if (!function_exists('shell_exec')) return null;

    // Run from the project root regardless of the working directory of the request.
    $git = 'git -C ' . escapeshellarg(self::PROJECT_ROOT) . ' ';
    $identifier = shell_exec($git . 'describe --always --tags 2>/dev/null');
    $hash = shell_exec($git . 'rev-parse HEAD 2>/dev/null');
    $timestamp = shell_exec($git . 'log -n 1 --pretty=\'%aI\' HEAD 2>/dev/null');
    $license = shell_exec($git . 'log -n 1 --pretty=\'%h %aI\' HEAD -- LICENSE.txt 2>/dev/null');

    if (!empty($identifier)) $values[0] = trim($identifier);
    if (!empty($hash)) $values[1] = trim($hash);
    if (!empty($timestamp)) $values[2] = trim($timestamp);
    if (!empty($license)) $values[3] = trim($license);

    if (is_null($values[0]) && is_null($values[1]) && is_null($values[2]) && is_null($values[3]))
    {
      $values = null;
    }

    return $values;
  }
}
