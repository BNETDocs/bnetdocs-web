<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */

namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Router;

class Edit extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\News\Edit();
  }
  public function invoke(?array $args): bool
  {
    $this->model->acl_allowed = $this->model->active_user
      && $this->model->active_user->getOption(\BNETDocs\Libraries\User::OPTION_ACL_NEWS_MODIFY);

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = 403;
      $this->model->error = 'ACL_NOT_SET';
      return true;
    }

    $q = Router::query();
    $this->model->news_post_id = $q['id'] ?? null;

    try { $this->model->news_post = new \BNETDocs\Libraries\NewsPost($this->model->news_post_id); }
    catch (\UnexpectedValueException) { $this->model->news_post = null; }

    if (!$this->model->news_post)
    {
      $this->model->_responseCode = 404;
      $this->model->error = 'NOT_FOUND';
      return true;
    }

    $this->model->news_categories = \BNETDocs\Libraries\NewsCategory::getAll();
    usort($this->model->news_categories, function($a, $b){
      $oA = $a->getSortId();
      $oB = $b->getSortId();
      if ($oA == $oB) return 0;
      return ($oA < $oB) ? -1 : 1;
    });

    $this->model->comments = Comment::getAll(Comment::PARENT_TYPE_NEWS_POST, $this->model->news_post_id);
    $this->model->category = $this->model->news_post->getCategoryId();
    $this->model->content = $this->model->news_post->getContent(false);
    $this->model->markdown = $this->model->news_post->isMarkdown();
    $this->model->published = $this->model->news_post->isPublished();
    $this->model->rss_exempt = $this->model->news_post->isRSSExempt();
    $this->model->title = $this->model->news_post->getTitle();

    if (Router::requestMethod() == Router::METHOD_POST) $this->handlePost();

    $this->model->_responseCode = 200;
    return $this->model;
  }

  protected function handlePost() : void
  {
    $q = Router::query();
    $category = $q['category'] ?? null;
    $title = $q['title'] ?? null;
    $markdown = $q['markdown'] ?? null;
    $content = $q['content'] ?? null;
    $rss_exempt = $q['rss_exempt'] ?? null;
    $publish = $q['publish'] ?? null;

    $this->model->category = $category;
    $this->model->title = $title;
    $this->model->markdown = $markdown;
    $this->model->content = $content;
    $this->model->rss_exempt = $rss_exempt;

    $this->model->error = empty($title) ? 'EMPTY_TITLE' : (empty($content) ? 'EMPTY_CONTENT' : null);
    if ($this->model->error) return;

    $this->model->news_post->setCategoryId($this->model->category);
    $this->model->news_post->setTitle($this->model->title);
    $this->model->news_post->setMarkdown($this->model->markdown);
    $this->model->news_post->setContent($this->model->content);
    $this->model->news_post->setRSSExempt($this->model->rss_exempt);
    $this->model->news_post->setPublished($publish);
    $this->model->news_post->incrementEdited();

    $this->model->error = $this->model->news_post->commit() ? false : 'INTERNAL_ERROR';

    \BNETDocs\Libraries\Event::log(
      \BNETDocs\Libraries\EventTypes::NEWS_EDITED,
      $this->model->active_user,
      getenv('REMOTE_ADDR'),
      [
        'error' => $this->model->error,
        'news_post_id' => $this->model->news_post_id,
        'category_id' => $this->model->news_post->getCategoryId(),
        'options_bitmask' => $this->model->news_post->getOptionsBitmask(),
        'title' => $this->model->news_post->getTitle(),
        'content' => $this->model->news_post->getContent(false),
      ]
    );
  }
}
