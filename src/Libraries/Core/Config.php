<?php /* vim: set colorcolumn=: */

namespace BNETDocs\Libraries\Core;

use \BNETDocs\Libraries\Db\MariaDb;

class Config
{
  public const SKIP_NONE = 0;
  public const SKIP_DB = 1;
  public const SKIP_JSON = 2;

  public const JSON_FLAGS = \JSON_OBJECT_AS_ARRAY | \JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR;
  public const JSON_CONFIG_PATH = __DIR__ . '/../../../etc/config.phoenix.json';
  protected static mixed $json_config = null;

  /**
   * Private constructor that throws a LogicException if trying to create an object of this class.
   */
  private function __construct()
  {
    throw new \LogicException('This class should not be constructed');
  }

  /**
   * Loads a config value by looking up a key from the static JSON or database table.
   * Note if the JSON key exists but the value is set null, a database table lookup will be performed.
   * Note also if either the JSON or DB value is null for the key, the default is returned instead.
   * The database driver sets SKIP_DB so a loop does not occur retrieving database config from JSON.
   *
   * @param string $key The key string.
   * @param mixed $default The value to return if the key is not set.
   * @param integer $skip The skip flags, a bitwise combination of SKIP_DB and/or SKIP_JSON.
   * @return mixed The value from the database or the default value.
   */
  public static function get(string $key, mixed $default = null, int $skip = self::SKIP_NONE): mixed
  {
    if (($skip & self::SKIP_JSON) === 0)
    {
      if (!self::$json_config) self::loadJson();
      $r = self::getValueByKey($key, self::$json_config, null);
      if (!\is_null($r)) return $r;
    }

    if (($skip & self::SKIP_DB) === 0)
    {
      try
      {
        $db = MariaDb::instance();
        $p = $db->prepare('SELECT `json_value` FROM `config` WHERE `key_name` = ? LIMIT 1;');
        if (!$p || !$p->execute([$key]) || $p->rowCount() === 0) return $default;
        $r = \json_decode($p->fetchObject()->json_value, true);
        if (!\is_null($r)) return $r;
      }
      finally
      {
        if (isset($p) && $p instanceof \PDOStatement) $p->closeCursor();
      }
    }

    return $default;
  }

  /**
   * Gets a value in a heystack from a dot notation key string.
   *
   * @param string $key is a non-empty key string to find in the haystack.
   * @param array $haystack is a loopable array.
   * @param mixed $default is returned if the value cannot be found.
   * @return mixed The value from the haystack, or the default value if not found.
   */
  protected static function getValueByKey(string $key, array $haystack, mixed $default = null): mixed
  {
    if (!\is_string($key) || empty($key) || !count($haystack))
    {
      return $default;
    }

    if (\strpos($key, '.') !== false)
    {
      $keys = \explode('.', $key);

      foreach ($keys as $innerKey)
      {
        // @assert $haystack[$innerKey] is available to continue
        // @otherwise return $default value
        if (!\array_key_exists($innerKey, $haystack)) return $default;

        $haystack = $haystack[$innerKey];
      }

      return $haystack;
    }

    // @fallback returning value of $key in $haystack or $default value
    return \array_key_exists($key, $haystack) ? $haystack[$key] : $default;
  }

  /**
   * Reads the JSON configuration file from disk into application memory.
   *
   * @throws \UnexpectedValueException when the JSON configuration file is unavailable.
   */
  public static function loadJson(): void
  {
    self::$json_config = null;

    if (!\file_exists(self::JSON_CONFIG_PATH) || !\is_readable(self::JSON_CONFIG_PATH))
    {
      throw new \UnexpectedValueException('Cannot retrieve application JSON configuration');
    }

    $buffer = \file_get_contents(self::JSON_CONFIG_PATH);
    if ($buffer === false || empty($buffer))
    {
      throw new \UnexpectedValueException('Application JSON configuration is empty');
    }
    self::$json_config = \json_decode($buffer, true, 512, self::JSON_FLAGS);
  }

  /**
   * Saves a config value to the database.
   *
   * @param string $key The key string.
   * @param mixed $value The value to save to the database.
   * @return boolean Whether the operation was successful.
   */
  public static function set(string $key, mixed $value): bool
  {
    try
    {
      $q = MariaDb::instance()->prepare('
        INSERT INTO `config` (`key_name`, `json_value`)
        VALUES (:k, :v) ON DUPLICATE KEY UPDATE `json_value` = :v;
      ');
      $json = \json_encode($value);
      return $q && $q->execute([':k' => $key, ':v' => $json]);
    }
    finally { if (isset($q) && $q instanceof \PDOStatement) $q->closeCursor(); }
  }
}
