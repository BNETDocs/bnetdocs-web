<?php
namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\BlizzardCheck;
use \BNETDocs\Libraries\GeoIP;
use \BNETDocs\Libraries\SlackCheck;
use \BNETDocs\Libraries\VersionInfo;
use \BNETDocs\Models\Status as StatusModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Database;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\DateTime;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \DateTimeZone;
use \StdClass;

class Status extends Controller
{
  const MAX_USER_AGENT = 0xFF;

  /**
   * run()
   *
   * @return Model The model with data that can be rendered by a view.
   */
  public function &run(Router &$router, View &$view, array &$args)
  {
    $model = new StatusModel();
    $code = (self::getStatus($model) ? 200 : 500);
    $view->render($model);
    $model->_responseCode = $code;
    return $model;
  }

  /**
   * getStatus()
   *
   * @return bool Indicates summary health status, where true is healthy.
   */
  protected static function getStatus(StatusModel &$model)
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $remote_address = getenv('REMOTE_ADDR');
    $ua = substr(getenv('HTTP_USER_AGENT'), 0, self::MAX_USER_AGENT);
    $utc = new DateTimeZone('Etc/UTC');

    $model->status = [
      'healthcheck' => [
        'database' => (Common::$database instanceof Database),
      ],
      'is_blizzard' => BlizzardCheck::is_blizzard(),
      'is_slack' => SlackCheck::is_slack(),
      'remote_address' => $remote_address,
      'remote_geoinfo' => GeoIP::getRecord($remote_address),
      'remote_is_browser' => Common::isBrowser($ua),
      'remote_user_agent' => $ua,
      'timestamp' => new DateTime('now', $utc),
      'version_info' => VersionInfo::$version,
    ];

    foreach ( $model->status['healthcheck'] as $key => $val ) {
      if ( is_bool( $val ) && !$val ) {
        // let the controller know that we're unhealthy.
        return false;
      }
    }

    return true;
  }
}
