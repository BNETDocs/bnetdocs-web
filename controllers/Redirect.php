<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Redirect as RedirectModel;
use \BNETDocs\Views\RedirectHtml as RedirectHtmlView;
use \DateTime;
use \DateTimeZone;

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
      case "":
      case "htm":
      case "html":
        $view = new RedirectHtmlView();
        break;
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new RedirectModel($this->redirect_code, $this->redirect_to);
    ob_start();
    $view->render($model);
    $router->setResponseCode($this->redirect_code);
    $router->setResponseHeader("Cache-Control", "max-age=300");
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseHeader("Expires", (new DateTime("+300 second"))->setTimezone(new DateTimeZone("GMT"))->format("D, d M Y H:i:s e"));
    $router->setResponseHeader("Location", $this->redirect_to);
    $router->setResponseHeader("Pragma", "max-age=300");
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
