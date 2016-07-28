<?php

namespace BNETDocs\Controllers\Comment;

use \BNETDocs\Libraries\Comment as CommentLib;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\CommentNotFoundException;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Comment\Create as CreateModel;
use \BNETDocs\Views\Comment\CreateJSON as CreateJSONView;
use \UnexpectedValueException;

class Create extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "json": case "":
        $view = new CreateJSONView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new CreateModel();
    $model->user_session = UserSession::load($router);

    $code = 500;
    if (!$model->user_session) {
      $model->response = ["error" => "Unauthorized"];
      $code = 403;
    } else if ($router->getRequestMethod() !== "POST") {
      $router->setResponseHeader("Allow", "POST");
      $model->response = ["error" => "Method Not Allowed","allow" => ["POST"]];
      $code = 405;
    } else {
      $code = $this->createComment($router, $model);
    }

    ob_start();
    $view->render($model);
    $router->setResponseCode($code);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    if (!empty($model->origin) && $code >= 300 && $code <= 399) {
      $router->setResponseHeader("Location", $model->origin);
    }
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function createComment(Router &$router, CreateModel &$model) {
    $query   = $router->getRequestBodyArray();
    $p_id    = (isset($query["parent_id"  ]) ? $query["parent_id"  ] : null);
    $p_type  = (isset($query["parent_type"]) ? $query["parent_type"] : null);
    $content = (isset($query["content"    ]) ? $query["content"    ] : null);

    if ($p_id   !== null) $p_id   = (int) $p_id;
    if ($p_type !== null) $p_type = (int) $p_type;

    switch ($p_type) {
      case CommentLib::PARENT_TYPE_DOCUMENT:  $origin = "/document/"; break;
      case CommentLib::PARENT_TYPE_COMMENT:   $origin = "/comment/";  break;
      case CommentLib::PARENT_TYPE_NEWS_POST: $origin = "/news/";     break;
      case CommentLib::PARENT_TYPE_PACKET:    $origin = "/packet/";   break;
      case CommentLib::PARENT_TYPE_SERVER:    $origin = "/server/";   break;
      case CommentLib::PARENT_TYPE_USER:      $origin = "/user/";     break;
      default: throw new UnexpectedValueException("Parent type: " . $p_type);
    }
    $origin = Common::relativeUrlToAbsolute($origin . $p_id . "#comments");
    $model->origin = $origin;

    if (empty($content)) {
      $success = false;
    } else {
      $success = CommentLib::create(
        $p_type, $p_id, $model->user_session->user_id, $content
      );
    }

    $model->response = [
      "content"     => $content,
      "error"       => ($success ? false : true),
      "origin"      => $origin,
      "parent_id"   => $p_id,
      "parent_type" => $p_type
    ];

    Logger::logEvent(
      "comment_created_news", $model->user_session->user_id,
      getenv("REMOTE_ADDR"), json_encode($model->response)
    );

    return 303;
  }

}
