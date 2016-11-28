<?php

namespace BNETDocs\Models;

use \CarlBennett\MVC\Libraries\Model;
use \JsonSerializable;

class Status extends Model implements JsonSerializable {

  const EOL            = "\n";
  const STR_BOOL_FALSE = 'false';
  const STR_BOOL_TRUE  = 'true';

  public $healthcheck;
  public $remote_address;
  public $remote_geoinfo;
  public $timestamp;
  public $version_info;

  public function jsonSerialize() {
    return [
      'healthcheck'    => $this->healthcheck,
      'remote_address' => $this->remote_address,
      'remote_geoinfo' => $this->remote_geoinfo,
      'timestamp'      => [
        'iso'  =>       $this->timestamp->format('r'),
        'unix' => (int) $this->timestamp->format('U'),
      ],
      'version_info'   => $this->version_info,
    ];
  }

  public function __toString() {

    ob_start();

    foreach ($this->healthcheck as $key => $val) {
      if (is_bool($val)) {
        echo 'healthcheck_' . $key . ' '
          . ($val ? self::STR_BOOL_TRUE : self::STR_BOOL_FALSE)
          . self::EOL;
      } else if (is_null($val)) {
        echo 'healthcheck_' . $key . ' null' . self::EOL;
      } else if (is_scalar($val)) {
        echo 'healthcheck_' . $key . ' ' . $val . self::EOL;
      } else {
        echo 'healthcheck_' . $key . ' ' . gettype($val) . self::EOL;
      }
    }

    echo 'remote_address ' . $this->remote_address . self::EOL;

    if ($this->remote_geoinfo) {
      foreach ($this->remote_geoinfo as $key => $val) {
        if (!empty($val))
          echo 'remote_geoinfo_' . $key . ' ' . $val . self::EOL;
      }
    } else if (is_bool($this->remote_geoinfo)) {
      echo 'remote_geoinfo '
        . ($this->remote_geoinfo ? self::STR_BOOL_TRUE : self::STR_BOOL_FALSE)
        . self::EOL;
    } else if (is_null($this->remote_geoinfo)) {
      echo 'remote_geoinfo null' . self::EOL;
    } else {
      echo 'remote_geoinfo ' . gettype($this->remote_geoinfo) . self::EOL;
    }

    echo 'timestamp_iso ' . $this->timestamp->format('r') . self::EOL;
    echo 'timestamp_unix ' . $this->timestamp->format('U') . self::EOL;

    foreach ($this->version_info as $key => $val) {
      if (!is_scalar($val)) {
        foreach ($val as $subkey => $subval) {
          if (is_bool($subval)) {
            echo 'version_info_' . $key . '_' . $subkey . ' '
              . ($subval ? self::STR_BOOL_TRUE : self::STR_BOOL_FALSE)
              . self::EOL;
          } else {
            echo 'version_info_' . $key . '_' . $subkey . ' ' . $subval
              . self::EOL;
          }
        }
      } else if (is_bool($val)) {
        echo 'version_info_' . $key . ' '
          . ($val ? self::STR_BOOL_TRUE : self::STR_BOOL_FALSE)
          . self::EOL;
      } else {
        echo 'version_info_' . $key . ' ' . $val . self::EOL;
      }
    }

    return ob_get_clean();
  }

}
