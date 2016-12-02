<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Controllers\RedirectSoft as RedirectSoftController;
use \BNETDocs\Libraries\Attachment;
use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\Exceptions\DocumentNotFoundException;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Document\View as DocumentViewModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View as ViewLib;
use \DateTime;
use \DateTimeZone;

class View extends Controller {

  public function &run(Router &$router, ViewLib &$view, array &$args) {

    $model              = new DocumentViewModel();
    $model->document_id = array_shift($args);

    try {
      $model->document  = new Document($model->document_id);
    } catch (DocumentNotFoundException $e) {
      $model->document  = null;
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

    $view->render($model);

    $model->_responseCode = ($model->document ? 200 : 404);
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
