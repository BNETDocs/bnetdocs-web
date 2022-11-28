<?php

namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\NewsCategory;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\Router;

class Create extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\News\Create();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args) : bool
  {
    $this->model->acl_allowed = $this->model->active_user
      && $this->model->active_user->getOption(\BNETDocs\Libraries\User::OPTION_ACL_NEWS_CREATE);

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = 403;
      $this->model->error = 'ACL_NOT_SET';
      return true;
    }

    $this->model->news_categories = NewsCategory::getAll();
    usort($this->model->news_categories, function($a, $b){
      $oA = $a->getSortId();
      $oB = $b->getSortId();
      if ($oA == $oB) return 0;
      return ($oA < $oB) ? -1 : 1;
    });

    if (Router::requestMethod() == Router::METHOD_POST)
    {
      $this->handlePost();
    }
    else if (Router::requestMethod() == Router::METHOD_GET)
    {
      $this->model->error = '';
      $this->model->markdown = true;
      $this->model->rss_exempt = false;
    }

    $this->model->_responseCode = 200;
    return true;
  }

  protected function handlePost() : void
  {
    $q = Router::query();
    $publish = $q['publish'] ?? null;

    $this->model->category = (int) ($q['category'] ?? null);
    $this->model->content = $q['content'] ?? '';
    $this->model->markdown = (bool) ($q['markdown'] ?? null);
    $this->model->rss_exempt = (bool) ($q['rss_exempt'] ?? null);
    $this->model->title = $q['title'] ?? '';

    if (empty($title))
    {
      $this->model->error = 'EMPTY_TITLE';
    }
    else if (empty($content))
    {
      $this->model->error = 'EMPTY_CONTENT';
    }
    else
    {
      $this->model->news_post = new NewsPost(null);

      $this->model->news_post->setCategoryId($this->model->category);
      $this->model->news_post->setContent($this->model->content);
      $this->model->news_post->setMarkdown($this->model->markdown);
      $this->model->news_post->setPublished($publish);
      $this->model->news_post->setRSSExempt($this->model->rss_exempt);
      $this->model->news_post->setTitle($this->model->title);

      $this->model->error = $this->model->news_post->commit() ? false : 'INTERNAL_ERROR';
    }

    \BNETDocs\Libraries\Event::log(
      \BNETDocs\Libraries\EventTypes::NEWS_CREATED,
      $this->model->active_user,
      getenv('REMOTE_ADDR'),
      [
        'error'           => $this->model->error,
        'category_id'     => $this->model->category,
        'options_bitmask' => $this->model->options_bitmask,
        'title'           => $this->model->title,
        'content'         => $this->model->content,
      ]
    );
  }
}
