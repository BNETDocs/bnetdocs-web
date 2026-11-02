<?php

namespace BNETDocs\Controllers\Core;

use \BNETDocs\Libraries\Core\Config;
use \BNETDocs\Libraries\Core\HttpCode;
use \BNETDocs\Models\Core\Status as StatusModel;

class Status extends \BNETDocs\Controllers\Base
{
  public const MAX_USER_AGENT = 255; // database varchar(255)

  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new StatusModel();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args): bool
  {
    $code = (self::getStatus($this->model) ? HttpCode::HTTP_OK : HttpCode::HTTP_INTERNAL_SERVER_ERROR);
    $this->model->_responseCode = $code;
    return true;
  }

  /**
   * getStatus()
   *
   * @return bool Indicates summary health status, where true is healthy.
   */
  protected static function getStatus(StatusModel $model) : bool
  {
    $remote_address = getenv('REMOTE_ADDR') ?: '127.0.0.1';
    $ua = substr(getenv('HTTP_USER_AGENT') ?: '', 0, self::MAX_USER_AGENT);
    $utc = new \DateTimeZone('Etc/UTC');

    $geoip_enabled = Config::get('geoip.enabled');
    $model->status = [
      'healthcheck' => [
        'database' => (!is_null(\BNETDocs\Libraries\Db\MariaDb::instance())),
        'geoip' => $geoip_enabled && file_exists(Config::get('geoip.database_file')),
      ],
      'remote_address' => $remote_address,
      'remote_geoinfo' => $geoip_enabled ? \BNETDocs\Libraries\Core\GeoIP::getRecord($remote_address) : null,
      'remote_is_blizzard' => \BNETDocs\Libraries\Core\BlizzardCheck::is_blizzard(),
      'remote_is_browser' => \BNETDocs\Libraries\Core\StringProcessor::isBrowser($ua),
      'remote_is_slack' => \BNETDocs\Libraries\Core\SlackCheck::is_slack(),
      'remote_user_agent' => $ua,
      'timestamp' => new \BNETDocs\Libraries\Core\DateTimeImmutable('now', $utc),
      'version_info' => \BNETDocs\Libraries\Core\VersionInfo::get(),
    ];

    foreach ($model->status['healthcheck'] as $key => $val)
    {
      if (is_bool($val) && !$val)
      {
        // let the controller know that we're unhealthy.
        return false;
      }
    }

    return true;
  }
}
