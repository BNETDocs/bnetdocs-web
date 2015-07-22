<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Status as StatusModel;
use \BNETDocs\Views\StatusJSON as StatusJSONView;
use \BNETDocs\Views\StatusPlain as StatusPlainView;

class Status extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "txt":
      case "text":
        $view = new StatusPlainView();
        break;
      case "":
      case "json":
        $view = new StatusJSONView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new StatusModel();
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
