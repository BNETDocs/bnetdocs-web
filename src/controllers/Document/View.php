<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Controllers\Redirect as RedirectController;
use \BNETDocs\Libraries\Attachment;
use \BNETDocs\Libraries\Comment;
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
    $model              = new DocumentViewModel();
    $model->document_id = (int) $this->document_id;
    try {
      $model->document  = new Document($this->document_id);
    } catch (DocumentNotFoundException $e) {
      $model->document  = null;
    }
    $pathArray = $router->getRequestPathArray();
    if ($model->document && (
      !isset($pathArray[3]) || empty($pathArray[3]))) {
      $redirect = new RedirectController(
        Common::relativeUrlToAbsolute(
          "/document/" . $model->document->getId() . "/"
          . Common::sanitizeForUrl(
            $model->document->getTitle(), true
          )
        ), 302
      );
      return $redirect->run($router);
    }
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new DocumentViewHtmlView();
      break;
      case "md": case "txt":
        $view = new DocumentViewPlainView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    if ($model->document) {
      $model->attachments = Attachment::getAll(
        Comment::PARENT_TYPE_DOCUMENT,
        $model->document_id
      );
      $model->comments = Comment::getAll(
        Comment::PARENT_TYPE_DOCUMENT,
        $model->document_id
      );
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
