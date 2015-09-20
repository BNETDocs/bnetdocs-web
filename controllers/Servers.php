<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Server as ServerLib;
use \BNETDocs\Libraries\ServerType as ServerTypeLib;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Servers as ServersModel;
use \BNETDocs\Views\ServersHtml as ServersHtmlView;
use \BNETDocs\Views\ServersJSON as ServersJSONView;

class Servers extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new ServersHtmlView();
      break;
      case "json":
        $view = new ServersJSONView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new ServersModel();
    $this->getServers($model);
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(300);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function getServers(ServersModel &$model) {
    $model->servers      = ServerLib::getAllServers();
    $model->server_types = ServerTypeLib::getAllServerTypes();
  }

}
