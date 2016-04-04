<?php

namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\News\Create as NewsCreateModel;
use \BNETDocs\Views\News\CreateHtml as NewsCreateHtmlView;

class Create extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new NewsCreateHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model               = new NewsCreateModel();
    $model->user_session = UserSession::load($router);
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
