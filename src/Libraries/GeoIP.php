<?php
namespace BNETDocs\Libraries;

use \CarlBennett\MVC\Libraries\Common;

class GeoIP
{
  private static $reader;

  private function __construct() {}

  /**
   * Quick safe method to get the ISO country code for the address given. Returns null if unavailable.
   *
   * @param ?string $address
   * @return ?string
   */
  public static function getCountryISOCode(?string $address = null): ?string
  {
    $ip = $address ?? '';
    if (empty($ip)) $ip = \getenv('REMOTE_ADDR');

    $record = self::getRecord($ip);
    if (!$record) return null;
    if (!isset($record->country) || !$record->country) return null;
    if (!isset($record->country->isoCode) || !$record->country->isoCode) return null;

    return $record->country->isoCode;
  }

  /**
   * Converts ISO 3166-1 alpha-2 country code to the country flag emoji Unicode character sequence.
   * <https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2>
   *
   * @param string $countryIsoAlpha2
   * @param ?string $extLeft
   * @param ?string $extRight
   * @return string
   */
  public static function getCountryFlagEmoji(string $countryIsoAlpha2, ?string $extLeft = null, ?string $extRight = null): string
  {
    $unicodePrefix = "\xF0\x9F\x87";
    $unicodeAdditionForLowerCase = 0x45;
    $unicodeAdditionForUpperCase = 0x65;

    if (\preg_match('/^[A-Z]{2}$/', $countryIsoAlpha2))
    {
      $emoji = $unicodePrefix . \chr(\ord($countryIsoAlpha2[0]) + $unicodeAdditionForUpperCase)
              . $unicodePrefix . \chr(\ord($countryIsoAlpha2[1]) + $unicodeAdditionForUpperCase);
    }
    elseif (\preg_match('/^[a-z]{2}$/', $countryIsoAlpha2))
    {
      $emoji = $unicodePrefix . \chr(\ord($countryIsoAlpha2[0]) + $unicodeAdditionForLowerCase)
              . $unicodePrefix . \chr(\ord($countryIsoAlpha2[1]) + $unicodeAdditionForLowerCase);
    }
    else
    {
      $emoji = '';
    }

    return \strlen($emoji) ? ($extLeft ?? '') . $emoji . ($extRight ?? '') : '';
  }

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

  public static function getRecord(string $address): ?\GeoIp2\Model\AbstractModel
  {
    if (!\filter_var($address, FILTER_VALIDATE_IP))
    {
      throw new \UnexpectedValueException('not a valid IP address');
    }

    if (!Common::$config->geoip->enabled) return null;

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
