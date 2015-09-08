<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Redirect as RedirectModel;
use \BNETDocs\Views\RedirectHtml as RedirectHtmlView;

class Redirect extends Controller {

  protected $redirect_code;
  protected $redirect_to;

  public function __construct($redirect_to, $redirect_code = 302) {
    parent::__construct();
    $this->redirect_code = $redirect_code;
    $this->redirect_to   = $redirect_to;
  }

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new RedirectHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new RedirectModel($this->redirect_code, $this->redirect_to);
    ob_start();
    $view->render($model);
    $router->setResponseCode($this->redirect_code);
    $router->setResponseTTL(300);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseHeader("Location", $this->redirect_to);
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
