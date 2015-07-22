<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Credits as CreditsModel;
use \BNETDocs\Views\CreditsHtml as CreditsHtmlView;

class Credits extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "":
      case "htm":
      case "html":
        $view = new CreditsHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $model = new CreditsModel();
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseHeader("Cache-Control", "max-age=300");
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseHeader("Expires", (new \DateTime("+300 second"))->setTimezone(new \DateTimeZone("GMT"))->format("D, d M Y H:i:s e"));
    $router->setResponseHeader("Pragma", "max-age=300");
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
