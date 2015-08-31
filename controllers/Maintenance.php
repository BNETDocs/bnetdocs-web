<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Maintenance as MaintenanceModel;
use \BNETDocs\Views\MaintenanceHtml as MaintenanceHtmlView;

class Maintenance extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "":
      case "htm":
      case "html":
        $view = new MaintenanceHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new MaintenanceModel();
    ob_start();
    $view->render($model);
    $router->setResponseCode(503);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
