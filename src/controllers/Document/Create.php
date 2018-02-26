<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\CSRF;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\Document\Create as DocumentCreateModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Create extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model               = new DocumentCreateModel();
    $model->csrf_id      = mt_rand();
    $model->csrf_token   = CSRF::generate($model->csrf_id, 7200); // 2 hours
    $model->error        = null;
    $model->user = (
      isset($_SESSION['user_id']) ? new User($_SESSION['user_id']) : null
    );

    $model->acl_allowed = ($model->user && $model->user->getAcl(
      User::OPTION_ACL_DOCUMENT_CREATE
    ));

    if ($router->getRequestMethod() == "POST") {
      $this->handlePost($router, $model);
    } else if ($router->getRequestMethod() == "GET") {
      $model->markdown = true;
    }

    $view->render($model);

    $model->_responseCode = ($model->acl_allowed ? 200 : 403);
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

  protected function handlePost(Router &$router, DocumentCreateModel &$model) {
    if (!$model->acl_allowed) {
      $model->error = "ACL_NOT_SET";
      return;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $data       = $router->getRequestBodyArray();
    $csrf_id    = (isset($data["csrf_id"   ]) ? $data["csrf_id"   ] : null);
    $csrf_token = (isset($data["csrf_token"]) ? $data["csrf_token"] : null);
    $csrf_valid = CSRF::validate($csrf_id, $csrf_token);
    $title      = (isset($data["title"     ]) ? $data["title"     ] : null);
    $markdown   = (isset($data["markdown"  ]) ? $data["markdown"  ] : null);
    $content    = (isset($data["content"   ]) ? $data["content"   ] : null);
    $publish    = (isset($data["publish"   ]) ? $data["publish"   ] : null);
    $save       = (isset($data["save"      ]) ? $data["save"      ] : null);

    $model->title    = $title;
    $model->markdown = $markdown;
    $model->content  = $content;

    if (!$csrf_valid) {
      $model->error = "INVALID_CSRF";
      return;
    }
    CSRF::invalidate($csrf_id);

    if (empty($title)) {
      $model->error = "EMPTY_TITLE";
    } else if (empty($content)) {
      $model->error = "EMPTY_CONTENT";
    }

    $options_bitmask = 0;
    if ($markdown) $options_bitmask |= Document::OPTION_MARKDOWN;
    if ($publish ) $options_bitmask |= Document::OPTION_PUBLISHED;

    $user_id = $model->user->getId();

    try {

      $success = Document::create(
        $user_id, $options_bitmask, $title, $content
      );

    } catch (QueryException $e) {

      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);

    }

    if (!$success) {
      $model->error = "INTERNAL_ERROR";
    } else {
      $model->error = false;
    }

    Logger::logEvent(
      EventTypes::DOCUMENT_CREATED,
      $user_id,
      getenv("REMOTE_ADDR"),
      json_encode([
        "error"           => $model->error,
        "options_bitmask" => $options_bitmask,
        "title"           => $title,
        "content"         => $content,
      ])
    );
  }

}
