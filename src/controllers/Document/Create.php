<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\CSRF;
use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\User;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Document\Create as DocumentCreateModel;
use \BNETDocs\Views\Document\CreateHtml as DocumentCreateHtmlView;

class Create extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new DocumentCreateHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model                  = new DocumentCreateModel();
    $model->csrf_id         = mt_rand();
    $model->csrf_token      = CSRF::generate($model->csrf_id, 900); // 15 mins
    $model->error           = null;
    $model->user_session    = UserSession::load($router);
    $model->user            = (isset($model->user_session) ?
                               new User($model->user_session->user_id) : null);

    $model->acl_allowed  = ($model->user &&
      $model->user->getOptionsBitmask() & User::OPTION_ACL_DOCUMENT_CREATE
    );

    if ($router->getRequestMethod() == "POST") {
      $this->handlePost($router, $model);
    } else if ($router->getRequestMethod() == "GET") {
      $model->markdown = true;
    }

    ob_start();
    $view->render($model);
    $router->setResponseCode(($model->acl_allowed ? 200 : 403));
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
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

    $user_id = $model->user_session->user_id;

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
      "document_created",
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
