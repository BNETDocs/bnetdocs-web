<?php

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Packet\Popular as PacketPopularModel;
use \BNETDocs\Views\Packet\PopularHtml as PacketPopularHtmlView;

class Popular extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new PacketPopularHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new PacketPopularModel();
    $model->user_session = UserSession::load($router);
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(300);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
