<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\User\Logout as UserLogoutModel;
use \BNETDocs\Views\User\LogoutHtml as UserLogoutHtmlView;

class Logout extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new UserLogoutHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new UserLogoutModel();
    $model->user_session = UserSession::load($router);
    if (isset($model->user_session)) {
      $model->user_session->invalidate($router);
      $model->user_session = null;
    }
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
