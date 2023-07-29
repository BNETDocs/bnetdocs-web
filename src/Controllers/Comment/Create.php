<?php

namespace BNETDocs\Controllers\Comment;

use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Router;

class Create extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Comment\Create();
  }

  public function invoke(?array $args): bool
  {
    $this->model->acl_allowed = $this->model->active_user
      && $this->model->active_user->getOption(\BNETDocs\Libraries\User::OPTION_ACL_COMMENT_CREATE);

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = 403;
      $this->model->response = ['error' => 'Unauthorized'];
    }
    else if (Router::requestMethod() !== Router::METHOD_POST)
    {
      $this->model->_responseCode = 405;
      $this->model->_responseHeaders['Allow'] = Router::METHOD_POST;
      $this->model->response = ['error' => 'Method Not Allowed', 'allow' => [Router::METHOD_POST]];
    }
    else
    {
      $this->model->_responseCode = $this->createComment();
    }

    if (!empty($this->model->origin) && $this->model->_responseCode >= 300 && $this->model->_responseCode <= 399)
      $this->model->_responseHeaders['Location'] = $this->model->origin;

    return true;
  }

  protected function createComment(): int
  {
    $q = Router::query();
    $pid = (isset($q['parent_id']) ? (int) $q['parent_id'] : null);
    $pt = (isset($q['parent_type']) ? (int) $q['parent_type'] : null);
    $content = $q['content'] ?? null;

    if (empty($content))
    {
      $this->model->error = 'EMPTY_CONTENT';
      return 400;
    }

    $this->model->comment = new Comment(null);
    $this->model->comment->setContent($content);

    $this->model->comment->setParentId($pid);
    $this->model->comment->setParentType($pt);
    $this->model->origin = $this->model->comment->getParentUrl();

    $this->model->comment->setUserId($this->model->active_user);

    $this->model->error = $this->model->comment->commit() ? false : 'INTERNAL_ERROR';
    $this->model->response = [
      'content' => $content,
      'error' => $this->model->error,
      'origin' => $this->model->origin,
      'parent_id' => $pid,
      'parent_type' => $pt
    ];

    \BNETDocs\Libraries\EventLog\Event::log(
      $this->model->comment->getParentTypeCreatedEventId(), $this->model->active_user, getenv('REMOTE_ADDR'), $this->model->response
    );

    return 303;
  }
}
