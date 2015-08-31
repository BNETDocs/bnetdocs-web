<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\User\Login as UserLoginModel;
use \BNETDocs\Views\User\LoginHtml as UserLoginHtmlView;

class Login extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "":
      case "htm":
      case "html":
        $view = new UserLoginHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new UserLoginModel();
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(300);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function tryLogin(Router &$router, UserLoginModel &$model) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $data            = $router->getRequestBodyArray();
    $model->email    = (isset($data["email"   ]) ? $data["email"   ] : null);
    $model->password = (isset($data["password"]) ? $data["password"] : null);
    if (empty($model->email)) {
      $model->login_result = "Email address was left blank.";
    } else {
      $model->login_result = "Incorrect username or password.";
    }
  }

}
