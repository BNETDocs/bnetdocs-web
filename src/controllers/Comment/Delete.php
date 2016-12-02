<?php

namespace BNETDocs\Controllers\Comment;

use \BNETDocs\Libraries\CSRF;
use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Exceptions\CommentNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\User;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Comment\Delete as CommentDeleteModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \InvalidArgumentException;
use \UnexpectedValueException;

class Delete extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $data                = $router->getRequestQueryArray();
    $model               = new CommentDeleteModel();
    $model->comment      = null;
    $model->csrf_id      = mt_rand();
    $model->csrf_token   = CSRF::generate($model->csrf_id);
    $model->error        = null;
    $model->id           = (isset($data["id"]) ? $data["id"] : null);
    $model->parent_id    = null;
    $model->parent_type  = null;
    $model->user_session = UserSession::load($router);
    $model->user         = (isset($model->user_session) ?
                            new User($model->user_session->user_id) : null);

    try { $model->comment = new Comment($model->id); }
    catch (CommentNotFoundException $e) { $model->comment = null; }
    catch (InvalidArgumentException $e) { $model->comment = null; }

    $model->acl_allowed = ($model->user &&
      ($model->user->getOptionsBitmask() & User::OPTION_ACL_COMMENT_DELETE)
      || ($model->user_session->user_id == $model->comment->getUserId())
    );

    if ($model->comment === null) {
      $model->error = "NOT_FOUND";
    } else {
      $model->content     = $model->comment->getContent(true);
      $model->parent_type = $model->comment->getParentType();
      $model->parent_id   = $model->comment->getParentId();

      if ($router->getRequestMethod() == "POST") {
        $this->tryDelete($router, $model);
      }
    }

    $view->render($model);

    $model->_responseCode = ($model->acl_allowed ? 200 : 403);
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;
  }

  protected function tryDelete(Router &$router, CommentDeleteModel &$model) {
    if (!isset($model->user_session)) {
      $model->error = "NOT_LOGGED_IN";
      return;
    }
    if (!$model->acl_allowed) {
      $model->error = "ACL_NOT_SET";
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
    $parent_type  = (int) $model->parent_type;
    $parent_id    = (int) $model->parent_id;
    $user_id      = $model->user_session->user_id;

    $log_key = "";
    switch ($parent_type) {
      case Comment::PARENT_TYPE_DOCUMENT:  $log_key = "_document"; break;
      case Comment::PARENT_TYPE_COMMENT:   $log_key = "_comment";  break;
      case Comment::PARENT_TYPE_NEWS_POST: $log_key = "_news";     break;
      case Comment::PARENT_TYPE_PACKET:    $log_key = "_packet";   break;
      case Comment::PARENT_TYPE_SERVER:    $log_key = "_server";   break;
      case Comment::PARENT_TYPE_USER:      $log_key = "_user";     break;
      default: throw new UnexpectedValueException(
        "Parent type: " . $parent_type
      );
    }

    try {

      $success = Comment::delete($id, $parent_type, $parent_id);

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
      "comment_deleted" . $log_key,
      $user_id,
      getenv("REMOTE_ADDR"),
      json_encode([
        "error"       => $model->error,
        "comment_id"  => $id,
        "parent_type" => $parent_type,
        "parent_id"   => $parent_id
      ])
    );
  }

}
