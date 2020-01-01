<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\CSRF;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\DocumentNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\Document\Delete as DocumentDeleteModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \InvalidArgumentException;

class Delete extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $data                = $router->getRequestQueryArray();
    $model               = new DocumentDeleteModel();
    $model->csrf_id      = mt_rand();
    $model->csrf_token   = CSRF::generate($model->csrf_id);
    $model->document     = null;
    $model->error        = null;
    $model->id           = (isset($data['id']) ? $data['id'] : null);
    $model->title        = null;
    $model->user         = Authentication::$user;

    $model->acl_allowed = ($model->user && $model->user->getAcl(
      User::OPTION_ACL_DOCUMENT_DELETE
    ));

    try { $model->document = new Document($model->id); }
    catch (DocumentNotFoundException $e) { $model->document = null; }
    catch (InvalidArgumentException $e) { $model->document = null; }

    if ($model->document === null) {
      $model->error = 'NOT_FOUND';
    } else {
      $model->title = $model->document->getTitle();

      if ($router->getRequestMethod() == 'POST') {
        $this->tryDelete($router, $model);
      }
    }

    $view->render($model);
    $model->_responseCode = ($model->acl_allowed ? 200 : 403);
    return $model;
  }

  protected function tryDelete(Router &$router, DocumentDeleteModel &$model) {
    if (!isset($model->user)) {
      $model->error = 'NOT_LOGGED_IN';
      return;
    }

    $data       = $router->getRequestBodyArray();
    $csrf_id    = (isset($data['csrf_id'   ]) ? $data['csrf_id'   ] : null);
    $csrf_token = (isset($data['csrf_token']) ? $data['csrf_token'] : null);
    $csrf_valid = CSRF::validate($csrf_id, $csrf_token);

    if (!$csrf_valid) {
      $model->error = 'INVALID_CSRF';
      return;
    }
    CSRF::invalidate($csrf_id);

    if (!$model->acl_allowed) {
      $model->error = 'ACL_NOT_SET';
      return;
    }

    $model->error = false;

    $id      = (int) $model->id;
    $user_id = $model->user->getId();

    try {

      $success = Document::delete($id);

    } catch (QueryException $e) {

      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);

      $success = false;

    }

    if (!$success) {
      $model->error = 'INTERNAL_ERROR';
    } else {
      $model->error = false;
    }

    Logger::logEvent(
      EventTypes::DOCUMENT_DELETED,
      $user_id,
      getenv('REMOTE_ADDR'),
      json_encode([
        'error'       => $model->error,
        'document_id' => $id,
      ])
    );
  }
}
