<?php

namespace BNETDocs\Controllers\Comment;

use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Libraries\Router;

class Edit extends \BNETDocs\Controllers\Base
{
  public const ACL_NOT_SET = 'ACL_NOT_SET';
  public const NOT_FOUND = 'NOT_FOUND';
  public const NOT_LOGGED_IN = 'NOT_LOGGED_IN';
  public const INTERNAL_ERROR = 'INTERNAL_ERROR';

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
      $this->model->error = $this->model->active_user ? self::ACL_NOT_SET : self::NOT_LOGGED_IN;
      return true;
    }

    if (!$this->model->comment)
    {
      $this->model->_responseCode = 404;
      $this->model->error = self::NOT_FOUND;
      return true;
    }

    $this->model->_responseCode = 200;
    $this->model->parent_id = $this->model->comment->getParentId();
    $this->model->parent_type = $this->model->comment->getParentType();
    $this->model->return_url = $this->model->comment->getParentUrl();
    if (is_null($this->model->content)) $this->model->content = $this->model->comment->getContent(false);

    if (Router::requestMethod() == Router::METHOD_POST) $this->tryEdit();
    return true;
  }

  protected function tryEdit(): void
  {
    $this->model->comment->setContent($this->model->content);
    $this->model->comment->incrementEdited();

    if (!$this->model->comment->commit())
    {
      $this->model->error = self::INTERNAL_ERROR;
      return;
    }

    $this->model->error = false;

    $event = Logger::initEvent(
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

    if ($event->commit())
    {
      $comment_user = $this->model->comment->getUser();
      $fields = [];
      if (!\is_null($comment_user)) $fields['Authored by'] = $comment_user->getAsMarkdown();
      $fields['Edited by'] = $this->model->active_user->getAsMarkdown();
      $embed = Logger::initDiscordEmbed($event, $this->model->comment->getParentUrl() . '#comments', $fields);
      if (!\is_null($comment_user)) $embed->setAuthor($comment_user->getAsDiscordEmbedAuthor());
      $embed->setDescription($this->model->comment->getContent(false));
      Logger::logToDiscord($event, $embed);
    }
  }
}
