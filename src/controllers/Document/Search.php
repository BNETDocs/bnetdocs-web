<?php

namespace BNETDocs\Controllers\Document;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Document\Search as DocumentSearchModel;
use \BNETDocs\Views\Document\SearchHtml as DocumentSearchHtmlView;

class Search extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new DocumentSearchHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new DocumentSearchModel();
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
