<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Maintenance as MaintenanceModel;
use \BNETDocs\Views\MaintenanceHtml as MaintenanceHtmlView;
use \BNETDocs\Views\MaintenanceJSON as MaintenanceJSONView;
use \BNETDocs\Views\MaintenancePlain as MaintenancePlainView;

class Maintenance extends Controller {

  protected $message;

  public function __construct($message) {
    parent::__construct();
    $this->message = $message;
  }

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new MaintenanceHtmlView();
      break;
      case "json":
        $view = new MaintenanceJSONView();
      break;
      case "txt":
        $view = new MaintenancePlainView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new MaintenanceModel();
    $model->message = $this->message;
    ob_start();
    $view->render($model);
    $router->setResponseCode(503);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
