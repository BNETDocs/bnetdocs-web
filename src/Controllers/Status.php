<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Models\Status as StatusModel;
use \CarlBennett\MVC\Libraries\Common;

class Status extends Base
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
    $code = (self::getStatus($this->model) ? 200 : 500);
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
    $remote_address = getenv('REMOTE_ADDR') ?? '127.0.0.1';
    $ua = substr(getenv('HTTP_USER_AGENT') ?? '', 0, self::MAX_USER_AGENT);
    $utc = new \DateTimeZone('Etc/UTC');

    $model->status = [
      'healthcheck' => [
        'database' => (!is_null(\BNETDocs\Libraries\Database::instance())),
        'geoip' => Common::$config->geoip->enabled && file_exists(Common::$config->geoip->database_file),
      ],
      'remote_address' => $remote_address,
      'remote_geoinfo' => Common::$config->geoip->enabled ? \BNETDocs\Libraries\GeoIP::getRecord($remote_address) : null,
      'remote_is_blizzard' => \BNETDocs\Libraries\BlizzardCheck::is_blizzard(),
      'remote_is_browser' => Common::isBrowser($ua),
      'remote_is_slack' => \BNETDocs\Libraries\SlackCheck::is_slack(),
      'remote_user_agent' => $ua,
      'timestamp' => new \BNETDocs\Libraries\DateTimeImmutable('now', $utc),
      'version_info' => \BNETDocs\Libraries\VersionInfo::get(),
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
