<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\User\Register as UserRegisterModel;
use \BNETDocs\Views\User\RegisterHtml as UserRegisterHtmlView;

class Register extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "":
      case "htm":
      case "html":
        $view = new UserRegisterHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new UserRegisterModel();
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(300);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}