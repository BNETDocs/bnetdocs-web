<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\User as UserLib;
use \BNETDocs\Models\User\View as UserViewModel;
use \BNETDocs\Views\User\ViewHtml as UserViewHtmlView;

class View extends Controller {

  protected $user_id;

  public function __construct($user_id) {
    parent::__construct();
    $this->user_id = $user_id;
  }

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new UserViewHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new UserViewModel();
    $this->getUserInfo($model);
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(300);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function getUserInfo(UserViewModel &$model) {
    $model->user_id = $this->user_id;
    $model->user    = new UserLib($this->user_id);
  }

}
