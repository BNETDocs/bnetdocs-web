<?php
namespace BNETDocs\Libraries;

use \CarlBennett\MVC\Libraries\Common;
use \GeoIp2\Database\Reader;
use \GeoIp2\Exception\AddressNotFoundException;
use \MaxMind\Db\InvalidDatabaseException;

class GeoIP
{
  private static $reader;

  private function __construct() {}

  protected static function getReader()
  {
    if (self::$reader) return self::$reader;

    try
    {
      self::$reader = new Reader(Common::$config->geoip->database_file);
    }
    catch (InvalidDatabaseException $e)
    {
      // database is invalid or corrupt
      self::$reader = null;
    }

    return self::$reader;
  }

  public static function getRecord(string $address)
  {
    if (!filter_var($address, FILTER_VALIDATE_IP))
    {
      throw new UnexpectedValueException('not a valid IP address');
    }

    $mmdb = self::getReader();
    $type = Common::$config->geoip->database_type;

    try
    {
      $record = $mmdb->$type($address);
    }
    catch (AddressNotFoundException $e)
    {
      $record = null;
    }

    return $record;
  }
}
