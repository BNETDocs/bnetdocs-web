<?php

namespace BNETDocs\Controllers\Comment;

use \BNETDocs\Libraries\Router;

class Edit extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Comment\Edit();
  }

  public function invoke(?array $args): bool
  {
    $q = Router::query();
    $this->model->id = isset($q['id']) ? (int) $q['id'] : null;
    $this->model->content = $q['content'] ?? null;

    try { if (!is_null($this->model->id)) $this->model->comment = new \BNETDocs\Libraries\Comment($this->model->id); }
    catch (\UnexpectedValueException) { $this->model->comment = null; }

    $this->model->acl_allowed = $this->model->active_user && (
      $this->model->active_user->getOption(\BNETDocs\Libraries\User::OPTION_ACL_COMMENT_MODIFY) ||
      ($this->model->comment && $this->model->active_user->getId() === $this->model->comment->getUserId())
    );

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = 403;
      $this->model->error = $this->model->active_user ? 'ACL_NOT_SET' : 'NOT_LOGGED_IN';
      return true;
    }

    if (!$this->model->comment)
    {
      $this->model->_responseCode = 404;
      $this->model->error = 'NOT_FOUND';
      return true;
    }

    $this->model->_responseCode = 200;
    $this->model->parent_id = $this->model->comment->getParentId();
    $this->model->parent_type = $this->model->comment->getParentType();
    $this->model->return_url = $this->model->comment->getParentUrl();
    if (is_null($this->model->content)) $this->model->content = $this->model->comment->getContent(false);

    if (Router::requestMethod() == Router::METHOD_POST) $this->tryModify();
    return true;
  }

  protected function tryModify() : void
  {
    $this->model->comment->setContent($this->model->content);
    $this->model->comment->incrementEdited();

    $this->model->error = $this->model->comment->commit() ? false : 'INTERNAL_ERROR';

    \BNETDocs\Libraries\Event::log(
      $this->model->comment->getParentTypeEditedEventId(),
      $this->model->active_user,
      getenv('REMOTE_ADDR'),
      [
        'comment'     => $this->model->comment,
        'error'       => $this->model->error,
        'parent_type' => $this->model->parent_type,
        'parent_id'   => $this->model->parent_id
      ]
    );
  }
}
