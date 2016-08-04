<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\CSRF;
use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\Exceptions\DocumentNotFoundException;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\User;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Document\Delete as DocumentDeleteModel;
use \BNETDocs\Views\Document\DeleteHtml as DocumentDeleteHtmlView;
use \InvalidArgumentException;

class Delete extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new DocumentDeleteHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }

    $data                = $router->getRequestQueryArray();
    $model               = new DocumentDeleteModel();
    $model->csrf_id      = mt_rand();
    $model->csrf_token   = CSRF::generate($model->csrf_id);
    $model->document     = null;
    $model->error        = null;
    $model->id           = (isset($data["id"]) ? $data["id"] : null);
    $model->title        = null;
    $model->user_session = UserSession::load($router);
    $model->user         = (isset($model->user_session) ?
                            new User($model->user_session->user_id) : null);

    $model->acl_allowed = ($model->user &&
      $model->user->getOptionsBitmask() & User::OPTION_ACL_DOCUMENT_DELETE
    );

    try { $model->document = new Document($model->id); }
    catch (DocumentNotFoundException $e) { $model->document = null; }
    catch (InvalidArgumentException $e) { $model->document = null; }

    if ($model->document === null) {
      $model->error = "NOT_FOUND";
    } else {
      $model->title = $model->document->getTitle();

      if ($router->getRequestMethod() == "POST") {
        $this->tryDelete($router, $model);
      }
    }

    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function tryDelete(Router &$router, DocumentDeleteModel &$model) {
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

    if (!$model->acl_allowed) {
      $model->error = "ACL_NOT_SET";
      return;
    }

    $model->error = false;

    $id           = (int) $model->id;
    $user_id      = $model->user_session->user_id;

    try {

      $success = Document::delete($id);

    } catch (QueryException $e) {

      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);

      $success = false;

    }

    if (!$success) {
      $model->error = "INTERNAL_ERROR";
    } else {
      $model->error = false;
    }

    Logger::logEvent(
      "document_deleted",
      $user_id,
      getenv("REMOTE_ADDR"),
      json_encode([
        "error"       => $model->error,
        "document_id" => $id,
      ])
    );
  }

}
