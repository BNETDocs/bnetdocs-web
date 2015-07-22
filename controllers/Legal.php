<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Legal as LegalModel;
use \BNETDocs\Views\LegalHtml as LegalHtmlView;
use \BNETDocs\Views\LegalPlain as LegalPlainView;
use \DateTime;
use \DateTimeZone;

class Legal extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "":
      case "htm":
      case "html":
        $view = new LegalHtmlView();
      break;
      case "txt":
        $view = new LegalPlainView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new LegalModel();
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseHeader("Cache-Control", "max-age=300");
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseHeader("Expires", (new DateTime("+300 second"))->setTimezone(new DateTimeZone("GMT"))->format("D, d M Y H:i:s e"));
    $router->setResponseHeader("Pragma", "max-age=300");
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
