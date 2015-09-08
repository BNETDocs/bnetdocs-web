<?php

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Packet\Search as PacketSearchModel;
use \BNETDocs\Views\Packet\SearchHtml as PacketSearchHtmlView;

class Search extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new PacketSearchHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new PacketSearchModel();
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(300);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
