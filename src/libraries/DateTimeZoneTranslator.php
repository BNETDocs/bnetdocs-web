<?php

namespace BNETDocs\Libraries;

use \DateTime;
use \DateTimeZone;

class DateTimeZoneTranslator {

  public static function translate($datetime, $timezone) {

    if ($timezone instanceof DateTimeZone) {
      $tz = $timezone;
    } else {
      $tz = new DateTimeZone((string) $timezone);
    }

    if ($datetime instanceof DateTime) {
      $dt = clone $datetime;
    } else {
      $dt = new DateTime($datetime, new DateTimeZone("UTC"));
    }

    $dt->setTimezone($tz);

    return $dt;

  }

}
