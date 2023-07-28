<?php
namespace BNETDocs\Libraries;

use \CarlBennett\MVC\Libraries\Common;

class GeoIP
{
  private static $reader;

  private function __construct() {}

  protected static function getReader(): ?\GeoIp2\ProviderInterface
  {
    if (self::$reader) return self::$reader;

    try
    {
      self::$reader = new \GeoIp2\Database\Reader(Common::$config->geoip->database_file);
    }
    catch (\MaxMind\Db\Reader\InvalidDatabaseException $e)
    {
      // database is invalid or corrupt
      self::$reader = null;
    }

    return self::$reader;
  }

  public static function getRecord(string $address): mixed
  {
    if (!filter_var($address, FILTER_VALIDATE_IP))
      throw new \UnexpectedValueException('not a valid IP address');

    if (!Common::$config->geoip->enabled
      || !file_exists(Common::$config->geoip->database_file)) return null;

    $mmdb = self::getReader();
    $type = Common::$config->geoip->database_type;

    try
    {
      $record = $mmdb->$type($address);
    }
    catch (\GeoIp2\Exception\AddressNotFoundException)
    {
      $record = null;
    }

    return $record;
  }
}
