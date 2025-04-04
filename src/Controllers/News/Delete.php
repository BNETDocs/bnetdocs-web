<?php

namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\Core\HttpCode;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Models\News\Delete as DeleteModel;

class Delete extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new DeleteModel();
  }

  public function invoke(?array $args): bool
  {
    $this->model->acl_allowed = $this->model->active_user
      && $this->model->active_user->getOption(\BNETDocs\Libraries\User\User::OPTION_ACL_NEWS_DELETE);

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = HttpCode::HTTP_UNAUTHORIZED;
      $this->model->error = $this->model->active_user ? DeleteModel::ERROR_ACL_NOT_SET : DeleteModel::ERROR_NOT_LOGGED_IN;
      return true;
    }

    $q = Router::query();
    $this->model->id = isset($q['id']) ? (int) $q['id'] : null;

    try { if (!is_null($this->model->id)) $this->model->news_post = new \BNETDocs\Libraries\News\Post($this->model->id); }
    catch (\UnexpectedValueException) { $this->model->news_post = null; }

    if (!$this->model->news_post)
    {
      $this->model->_responseCode = HttpCode::HTTP_NOT_FOUND;
      $this->model->error = DeleteModel::ERROR_NOT_FOUND;
      return true;
    }

    $this->model->title = $this->model->news_post->getTitle();

    if (Router::requestMethod() == Router::METHOD_POST) $this->tryDelete();
    $this->model->_responseCode = $this->model->error ? HttpCode::HTTP_INTERNAL_SERVER_ERROR : HttpCode::HTTP_OK;
    return true;
  }

  protected function tryDelete(): void
  {
    $this->model->error = $this->model->news_post->deallocate() ? false : DeleteModel::ERROR_INTERNAL;
    if ($this->model->error !== false) return;

    $event = Logger::initEvent(
      \BNETDocs\Libraries\EventLog\EventTypes::NEWS_DELETED,
      $this->model->active_user,
      getenv('REMOTE_ADDR'),
      [
        'error' => $this->model->error,
        'news_post_id' => $this->model->id,
        'news_post' => $this->model->news_post,
      ]
    );

    if ($event->commit())
    {
      $content = $this->model->news_post->getContent(false);
      $embed = Logger::initDiscordEmbed($event, $this->model->news_post->getURI(), [
        'Category' => $this->model->news_post->getCategory()->getLabel(),
        'Title' => $this->model->news_post->getTitle(),
        'Markdown' => $this->model->news_post->isMarkdown() ? ':white_check_mark:' : ':x:',
        'RSS exempt' => $this->model->news_post->isRSSExempt() ? ':white_check_mark:' : ':x:',
        'Authored by' => !\is_null($this->model->news_post->getUserId()) ? $this->model->news_post->getUser()->getAsMarkdown() : '*Anonymous*',
        'Deleted by' => $this->model->active_user->getAsMarkdown(),
      ]);
      $desc = \substr($content, 0, \min(\BNETDocs\Libraries\Discord\Embed::MAX_DESCRIPTION - 13, \strlen($content) - 13));
      if (\strlen($desc) != \strlen($content)) $desc .= '…';
      if (!$this->model->news_post->isMarkdown()) $desc = '```' . \PHP_EOL . $desc . \PHP_EOL . '```';
      $embed->setDescription($desc);
      Logger::logToDiscord($event, $embed);
    }
  }
}
