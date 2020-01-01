<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\VersionInfo;
use \BNETDocs\Models\Status as StatusModel;
use \CarlBennett\MVC\Libraries\Cache;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Database;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\DateTime;
use \CarlBennett\MVC\Libraries\GeoIP;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \DateTimeZone;
use \StdClass;

class Status extends Controller {

  /**
   * run()
   *
   * @return Model The model with data that can be rendered by a view.
   */
  public function &run( Router &$router, View &$view, array &$args ) {

    $model = new StatusModel();
    $code  = ( self::getStatus( $model ) ? 200 : 500 );

    $view->render( $model );

    $model->_responseCode = $code;
    $model->_responseHeaders['Content-Type'] = $view->getMimeType();

    return $model;

  }

  /**
   * getStatus()
   *
   * @return bool Indicates summary health status, where true is healthy.
   */
  protected static function getStatus( StatusModel &$model ) {
    if ( !isset( Common::$database ) ) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $status = new StdClass();

    $healthcheck           = new StdClass();
    $healthcheck->database = ( Common::$database instanceof Database );
    $healthcheck->memcache = ( Common::$cache instanceof Cache );

    $utc = new DateTimeZone( 'Etc/UTC' );

    $status->healthcheck       = $healthcheck;
    $status->is_browser        = Common::isBrowser(getenv( 'HTTP_USER_AGENT' ));
    $status->remote_address    = getenv( 'REMOTE_ADDR' );
    $status->remote_geoinfo    = GeoIP::get( $status->remote_address );
    $status->remote_user_agent = getenv( 'HTTP_USER_AGENT' );
    $status->timestamp         = new DateTime( 'now', $utc );
    $status->version_info      = VersionInfo::$version;

    $model->status = $status;

    foreach ( $healthcheck as $key => $val ) {
      if ( is_bool( $val ) && !$val ) {
        // let the controller know that we're unhealthy.
        return false;
      }
    }

    return true;
  }

}
