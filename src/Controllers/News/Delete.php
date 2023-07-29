<?php

namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\Router;

class Delete extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\News\Delete();
  }

  public function invoke(?array $args): bool
  {
    $this->model->acl_allowed = $this->model->active_user
      && $this->model->user->getOption(\BNETDocs\Libraries\User::OPTION_ACL_NEWS_DELETE);

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = 401;
      $this->model->error = 'ACL_NOT_SET';
      return true;
    }

    $q = Router::query();
    $this->model->id = isset($q['id']) ? (int) $q['id'] : null;

    try { if (!is_null($this->model->id)) $this->model->news_post = new NewsPost($this->model->id); }
    catch (\UnexpectedValueException) { $this->model->news_post = null; }

    if (!$this->model->news_post)
    {
      $this->model->_responseCode = 404;
      $this->model->error = 'NOT_FOUND';
      return true;
    }

    $this->model->title = $this->model->news_post->getTitle();

    if (Router::requestMethod() == Router::METHOD_POST) $this->tryDelete();
    $this->model->_responseCode = $this->model->error ? 500 : 200;
    return true;
  }

  protected function tryDelete(): void
  {
    $this->model->error = $this->model->news_post->deallocate() ? false : 'INTERNAL_ERROR';

    \BNETDocs\Libraries\EventLog\Event::log(
      \BNETDocs\Libraries\EventLog\EventTypes::NEWS_DELETED,
      $this->model->active_user,
      getenv('REMOTE_ADDR'),
      [
        'error' => $this->model->error,
        'news_post_id' => $this->model->id,
        'news_post' => $this->model->news_post,
      ]
    );
  }
}
