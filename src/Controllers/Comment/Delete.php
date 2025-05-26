<?php

namespace BNETDocs\Controllers\Comment;

use \BNETDocs\Libraries\Core\HttpCode;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Models\Comment\Delete as DeleteModel;

class Delete extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new DeleteModel();
  }

  public function invoke(?array $args): bool
  {
    $this->model->id = Router::query()['id'] ?? null;

    try { $this->model->comment = new \BNETDocs\Libraries\Comment($this->model->id); }
    catch (\UnexpectedValueException) { $this->model->comment = null; }

    $this->model->acl_allowed = ($this->model->active_user && (
      $this->model->active_user->getOption(\BNETDocs\Libraries\User\User::OPTION_ACL_COMMENT_DELETE) ||
      ($this->model->comment && $this->model->active_user->getId() === $this->model->comment->getUserId())
    ));

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = HttpCode::HTTP_FORBIDDEN;
      $this->model->error = $this->model->active_user ? DeleteModel::ERROR_ACL_NOT_SET : DeleteModel::ERROR_NOT_LOGGED_IN;
      return true;
    }

    if (!$this->model->comment)
    {
      $this->model->_responseCode = HttpCode::HTTP_NOT_FOUND;
      $this->model->error = DeleteModel::ERROR_NOT_FOUND;
      return true;
    }

    $this->model->content = $this->model->comment->getContent(true);
    $this->model->parent_type = $this->model->comment->getParentType();
    $this->model->parent_id = $this->model->comment->getParentId();

    if (Router::requestMethod() == Router::METHOD_POST) $this->tryDelete();

    $this->model->_responseCode = !$this->model->error ? HttpCode::HTTP_OK : HttpCode::HTTP_INTERNAL_SERVER_ERROR;
    return true;
  }

  protected function tryDelete(): void
  {
    if (!$this->model->comment->deallocate())
    {
      $this->model->error = DeleteModel::ERROR_INTERNAL;
      return;
    }

    $this->model->error = false;

    $event = Logger::initEvent(
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

    if ($event->commit())
    {
      $comment_user = $this->model->comment->getUser();
      $fields = [];
      if (!\is_null($comment_user)) $fields['Authored by'] = $comment_user->getAsMarkdown();
      $fields['Deleted by'] = $this->model->active_user->getAsMarkdown();
      $embed = Logger::initDiscordEmbed($event, $this->model->comment->getParentUrl() . '#comments', $fields);
      if (!\is_null($comment_user)) $embed->setAuthor($comment_user->getAsDiscordEmbedAuthor());
      $embed->setDescription($this->model->comment->getContent(false));
      Logger::logToDiscord($event, $embed);
    }
  }
}
