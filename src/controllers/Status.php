<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\VersionInfo;
use \BNETDocs\Models\Status as StatusModel;
use \CarlBennett\MVC\Libraries\Cache;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Database;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\GeoIP;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \DateTime;
use \DateTimeZone;
use \StdClass;

class Status extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model = new StatusModel();
    $code  = (!$this->getStatus($model) ? 500 : 200);

    $view->render($model);

    $model->_responseCode = $code;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 300;

    return $model;

  }

  protected function getStatus(StatusModel &$model) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $healthcheck           = new StdClass();
    $healthcheck->database = (Common::$database instanceof Database);
    $healthcheck->memcache = (Common::$cache    instanceof Cache   );

    $model->healthcheck    = $healthcheck;
    $model->remote_address = getenv("REMOTE_ADDR");
    $model->remote_geoinfo = GeoIP::get($model->remote_address);
    $model->timestamp      = new DateTime("now", new DateTimeZone("UTC"));
    $model->version_info   = VersionInfo::$version;

    foreach ($healthcheck as $key => $val) {
      if (is_bool($val) && !$val) return false;
    }
    return true;
  }

}
