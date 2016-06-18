<?php

namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\CSRF;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\NewsPostNotFoundException;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\News\Delete as NewsDeleteModel;
use \BNETDocs\Views\News\DeleteHtml as NewsDeleteHtmlView;
use \InvalidArgumentException;

class Delete extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new NewsDeleteHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    
    $data                = $router->getRequestQueryArray();
    $model               = new NewsDeleteModel();
    $model->csrf_id      = mt_rand();
    $model->csrf_token   = CSRF::generate($model->csrf_id);
    $model->error        = null;
    $model->id           = (isset($data["id"]) ? $data["id"] : null);
    $model->news_post    = null;
    $model->title        = null;
    $model->user_session = UserSession::load($router);

    try { $model->news_post = new NewsPost($model->id); }
    catch (NewsPostNotFoundException $e) { $model->news_post = null; }
    catch (InvalidArgumentException $e) { $model->news_post = null; }
    
    if ($model->news_post === null) {
      $model->error = "NOT_FOUND";
    } else {
      $model->title = $model->news_post->getTitle();

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

  protected function tryDelete(Router &$router, NewsDeleteModel &$model) {
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

    $model->error = false;

    $id           = (int) $model->id;    
    $user_id      = $model->user_session->user_id;

    try {

      $success = NewsPost::delete($id);

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
      "news_deleted",
      $user_id,
      getenv("REMOTE_ADDR"),
      json_encode([
        "error"        => $model->error,
        "news_post_id" => $id,
      ])
    );
  }

}
