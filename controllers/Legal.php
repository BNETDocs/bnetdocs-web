<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Legal as LegalModel;
use \BNETDocs\Views\LegalHtml as LegalHtmlView;
use \BNETDocs\Views\LegalPlain as LegalPlainView;

class Legal extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new LegalHtmlView();
      break;
      case "txt":
        $view = new LegalPlainView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new LegalModel();
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
