<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Status as StatusModel;
use \BNETDocs\Views\StatusJSON as StatusJSONView;
use \BNETDocs\Views\StatusPlain as StatusPlainView;
use \DateTime;
use \DateTimeZone;

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
    $this->getStatus($model);
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(300);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function getStatus(StatusModel &$model) {
    $model->remote_address   = getenv("REMOTE_ADDR");
    $model->remote_geoinfo   = geoip_record_by_name($model->remote_address);
    $model->timestamp        = new DateTime("now", new DateTimeZone("UTC"));
    $model->timestamp_format = "r";
    $model->version_info     = Common::versionProperties();
    return true;
  }

}
