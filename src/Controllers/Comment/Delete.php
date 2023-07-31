<?php

namespace BNETDocs\Controllers\Comment;

use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Libraries\Router;

class Delete extends \BNETDocs\Controllers\Base
{
  public const ACL_NOT_SET = 'ACL_NOT_SET';
  public const INTERNAL_ERROR = 'INTERNAL_ERROR';
  public const NOT_FOUND = 'NOT_FOUND';

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
      $this->model->error = self::ACL_NOT_SET;
      return true;
    }

    if (!$this->model->comment)
    {
      $this->model->_responseCode = 404;
      $this->model->error = self::NOT_FOUND;
      return true;
    }

    $this->model->content = $this->model->comment->getContent(true);
    $this->model->parent_type = $this->model->comment->getParentType();
    $this->model->parent_id = $this->model->comment->getParentId();

    if (Router::requestMethod() == Router::METHOD_POST) $this->tryDelete();

    $this->model->_responseCode = 200;
    return true;
  }

  protected function tryDelete(): void
  {
    if (!$this->model->comment->deallocate())
    {
      $this->model->error = self::INTERNAL_ERROR;
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
