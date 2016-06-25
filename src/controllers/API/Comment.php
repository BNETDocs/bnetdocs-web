<?php

namespace BNETDocs\Controllers\API;

use \BNETDocs\Libraries\Comment as CommentLib;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\CommentNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\API\Comment as APICommentModel;
use \BNETDocs\Views\API\CommentJSON as APICommentJSONView;
use \DateTime;
use \DateTimeZone;

class Comment extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "json": case "":
        $view = new APICommentJSONView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new APICommentModel();
    $model->user_session = UserSession::load($router);

    switch ($router->getRequestMethod()) {
      case "GET":     $code = $this->handleGet($router, $model);     break;
      case "OPTIONS": $code = $this->handleOptions($router, $model); break;
      case "PUT":     $code = $this->handlePut($router, $model);     break;
      default:        $code = $this->handleUnknown($router, $model); break;
    }

    ob_start();
    $view->render($model);
    $router->setResponseCode($code);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function handleGet(Router &$router, APICommentModel &$model) {
    $query  = $router->getRequestQueryArray();
    $p_id   = (isset($query["parent_id"  ]) ? $query["parent_id"  ] : null);
    $p_type = (isset($query["parent_type"]) ? $query["parent_type"] : null);

    if ($p_id   !== null) $p_id   = (int) $p_id;
    if ($p_type !== null) $p_type = (int) $p_type;

    $comments = CommentLib::getAll($p_type, $p_id);
    $error    = ($comments === null ? true : false);

    $model->response = [
      "comments"    => $comments,
      "error"       => $error,
      "parent_id"   => $p_id,
      "parent_type" => $p_type,
    ];

    return ($error ? 400 : 200);
  }

  protected function handleOptions(Router &$router, APICommentModel &$model) {
    $router->setResponseHeader("Allow", "GET,OPTIONS,PUT");
    $model->response = ["error" => false,
      "allow" => ["GET","OPTIONS","PUT"]];
    return 200;
  }

  protected function handlePut(Router &$router, APICommentModel &$model) {
    return 403;
  }

  protected function handleUnknown(Router &$router, APICommentModel &$model) {
    $router->setResponseHeader("Allow", "GET,OPTIONS,PUT");
    $model->response = ["error" => "Method Not Allowed",
      "allow" => ["GET","OPTIONS","PUT"]];
    return 405;
  }

}
