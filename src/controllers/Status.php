<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\VersionInfo;
use \BNETDocs\Models\Status as StatusModel;
use \BNETDocs\Views\StatusJSON as StatusJSONView;
use \BNETDocs\Views\StatusPlain as StatusPlainView;
use \CarlBennett\MVC\Libraries\Cache;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Database;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\GeoIP;
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
    $model->remote_geoinfo = GeoIP::get($model->remote_address);
    $model->timestamp      = new DateTime("now", new DateTimeZone("UTC"));
    $model->version_info   = VersionInfo::$version;

    foreach ($healthcheck as $key => $val) {
      if (is_bool($val) && !$val) return false;
    }
    return true;
  }

}
