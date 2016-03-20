<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Document\View as DocumentViewModel;
use \BNETDocs\Views\Document\ViewHtml as DocumentViewHtmlView;
use \DateTime;
use \DateTimeZone;

class View extends Controller {

  protected $document_id;

  public function __construct($document_id) {
    parent::__construct();
    $this->document_id = $document_id;
  }

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new DocumentViewHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new DocumentViewModel();
    $model->document     = new Document($this->document_id);
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
