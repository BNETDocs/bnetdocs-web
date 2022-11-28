<?php

namespace BNETDocs\Controllers\Comment;

use \BNETDocs\Libraries\Router;

class Delete extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Comment\Delete();
  }

  public function invoke(?array $args): bool
  {
    $this->model->id = Router::query()['id'] ?? null;

    try { $this->model->comment = new \BNETDocs\Libraries\Comment($this->model->id); }
    catch (\UnexpectedValueException) { $this->model->comment = null; }

    $this->model->acl_allowed = ($this->model->active_user && (
      $this->model->active_user->getOption(\BNETDocs\Libraries\User::OPTION_ACL_COMMENT_DELETE) ||
      ($this->model->comment && $this->model->active_user->getId() == $this->model->comment->getUserId())
    ));

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = 403;
      $this->model->error = 'ACL_NOT_SET';
      return true;
    }

    if (!$this->model->comment)
    {
      $this->model->_responseCode = 404;
      $this->model->error = 'NOT_FOUND';
      return true;
    }

    $this->model->content = $this->model->comment->getContent(true);
    $this->model->parent_type = $this->model->comment->getParentType();
    $this->model->parent_id = $this->model->comment->getParentId();

    if (Router::requestMethod() == Router::METHOD_POST) $this->tryDelete();

    $this->model->_responseCode = 200;
    return true;
  }

  protected function tryDelete() : void
  {
    $this->model->error = $this->model->comment->deallocate() ? false : 'INTERNAL_ERROR';
    \BNETDocs\Libraries\Event::log(
      $this->model->comment->getParentTypeDeletedEventId(),
      $this->model->active_user,
      getenv('REMOTE_ADDR'),
      [
        'error' => $this->model->error,
        'comment' => $this->model->comment,
        'parent_type' => $this->model->parent_type,
        'parent_id' => $this->model->parent_id
      ]
    );
  }
}
