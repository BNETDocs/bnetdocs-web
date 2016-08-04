<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\VersionInfo;
use \BNETDocs\Models\Status as StatusModel;
use \BNETDocs\Views\StatusJSON as StatusJSONView;
use \BNETDocs\Views\StatusPlain as StatusPlainView;
use \CarlBennett\MVC\Libraries\Cache;
use \DateTime;
use \DateTimeZone;
use \StdClass;

class Status extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "txt":
        $view = new StatusPlainView();
      break;
      case "json": case "":
        $view = new StatusJSONView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new StatusModel();
    $code  = (!$this->getStatus($model) ? 500 : 200);
    ob_start();
    $view->render($model);
    $router->setResponseCode($code);
    $router->setResponseTTL(300);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
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
    $model->timestamp      = new DateTime("now", new DateTimeZone("UTC"));
    $model->version_info   = VersionInfo::$version;

    // geoip_record_by_name() triggers E_NOTICE if the IP cannot be found in
    // the local GeoIP database, and it also returns false. Since we don't give
    // two shits if it can't find the IP, especially since it returns false
    // instead of an array, we mute E_NOTICE temporarily below.

    $er = error_reporting(error_reporting() & ~E_NOTICE); // Disable E_NOTICE
    $model->remote_geoinfo = geoip_record_by_name($model->remote_address);
    error_reporting($er); // Re-enable E_NOTICE if it was previously enabled

    foreach ($healthcheck as $key => $val) {
      if (is_bool($val) && !$val) return false;
    }
    return true;
  }

}
