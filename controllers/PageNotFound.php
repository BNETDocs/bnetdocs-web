<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\PageNotFound as PageNotFoundModel;
use \BNETDocs\Views\PageNotFoundHtml as PageNotFoundHtmlView;
use \BNETDocs\Views\PageNotFoundJSON as PageNotFoundJSONView;
use \BNETDocs\Views\PageNotFoundPlain as PageNotFoundPlainView;

class PageNotFound extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new PageNotFoundHtmlView();
      break;
      case "json":
        $view = new PageNotFoundJSONView();
      break;
      case "txt":
        $view = new PageNotFoundPlainView();
      break;
      default:
        $view = new PageNotFoundHtmlView();
    }
    $model = new PageNotFoundModel();
    $model->user_session = UserSession::load($router);
    ob_start();
    $view->render($model);
    $router->setResponseCode(404);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
