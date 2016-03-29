<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\Exceptions\DocumentNotFoundException;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Document\View as DocumentViewModel;
use \BNETDocs\Views\Document\ViewHtml as DocumentViewHtmlView;
use \BNETDocs\Views\Document\ViewPlain as DocumentViewPlainView;
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
      case "txt":
        $view = new DocumentViewPlainView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new DocumentViewModel();
    $model->document_id  = $this->document_id;
    try {
      $model->document   = new Document($this->document_id);
    } catch (DocumentNotFoundException $e) {
      $model->document   = null;
    }
    $model->user_session = UserSession::load($router);
    ob_start();
    $view->render($model);
    $router->setResponseCode(($model->document ? 200 : 404));
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
