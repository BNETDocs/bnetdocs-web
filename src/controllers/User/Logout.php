<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\CSRF;
use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Logger;
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
    $model->csrf_id      = mt_rand();
    $model->csrf_token   = CSRF::generate($model->csrf_id);
    $model->error        = null;
    $model->user_session = UserSession::load($router);
    if ($router->getRequestMethod() == "POST") {
      $this->tryLogout($router, $model);
    }
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function tryLogout(Router &$router, UserLogoutModel &$model) {
    if (!isset($model->user_session)) {
      $model->error = "NOT_LOGGED_IN";
      return;
    }
    $data       = $router->getRequestBodyArray();
    $csrf_id    = (isset($data["csrf_id"   ]) ? $data["csrf_id"   ] : null);
    $csrf_token = (isset($data["csrf_token"]) ? $data["csrf_token"] : null);
    $csrf_valid = CSRF::validate($csrf_id, $csrf_token);
    if (!$csrf_valid) {
      $model->error = "INVALID_CSRF";
      return;
    }
    CSRF::invalidate($csrf_id);
    $model->error        = false;
    $user_id             = $model->user_session->user_id;
    $model->user_session->invalidate($router);
    $model->user_session = null;
    Logger::logEvent(
      "user_logout",
      $user_id,
      getenv("REMOTE_ADDR"),
      json_encode(["error" => $model->error])
    );
  }

}
