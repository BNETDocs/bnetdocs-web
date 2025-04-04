<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */

namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Core\HttpCode;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Models\News\Edit as EditModel;

class Edit extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new EditModel();
  }

  public function invoke(?array $args): bool
  {
    $this->model->acl_allowed = $this->model->active_user
      && $this->model->active_user->getOption(\BNETDocs\Libraries\User\User::OPTION_ACL_NEWS_MODIFY);

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = HttpCode::HTTP_FORBIDDEN;
      $this->model->error = $this->model->active_user ? EditModel::ERROR_ACL_NOT_SET : EditModel::ERROR_NOT_LOGGED_IN;
      return true;
    }

    $q = Router::query();
    $this->model->news_post_id = $q['id'] ?? null;

    try { $this->model->news_post = new \BNETDocs\Libraries\News\Post($this->model->news_post_id); }
    catch (\UnexpectedValueException) { $this->model->news_post = null; }

    if (!$this->model->news_post)
    {
      $this->model->_responseCode = HttpCode::HTTP_NOT_FOUND;
      $this->model->error = EditModel::ERROR_NOT_FOUND;
      return true;
    }

    $this->model->news_categories = \BNETDocs\Libraries\News\Category::getAll();
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

    $this->model->_responseCode = HttpCode::HTTP_OK;
    return true;
  }

  protected function handlePost(): void
  {
    $q = Router::query();
    $category = $q['category'] ?? null;
    $title = $q['title'] ?? null;
    $markdown = $q['markdown'] ?? null;
    $content = $q['content'] ?? null;
    $rss_exempt = $q['rss_exempt'] ?? null;
    $publish = $q['publish'] ?? null;

    $this->model->category = (int) $category;
    $this->model->title = $title;
    $this->model->markdown = (bool) $markdown;
    $this->model->content = $content;
    $this->model->rss_exempt = (bool) $rss_exempt;

    $this->model->error = empty($title) ? EditModel::ERROR_EMPTY_TITLE
      : (empty($content) ? EditModel::ERROR_EMPTY_CONTENT : null);
    if ($this->model->error) return;

    $this->model->news_post->setCategoryId($this->model->category);
    $this->model->news_post->setTitle($this->model->title);
    $this->model->news_post->setMarkdown($this->model->markdown);
    $this->model->news_post->setContent($this->model->content);
    $this->model->news_post->setRSSExempt($this->model->rss_exempt);
    $this->model->news_post->setPublished($publish);
    $this->model->news_post->incrementEdited();

    $this->model->error = $this->model->news_post->commit() ? false : EditModel::ERROR_INTERNAL;

    $event = Logger::initEvent(
      \BNETDocs\Libraries\EventLog\EventTypes::NEWS_EDITED,
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

    if ($event->commit())
    {
      $content = $this->model->news_post->getContent(false);
      $embed = Logger::initDiscordEmbed($event, $this->model->news_post->getURI(), [
        'Category' => $this->model->news_post->getCategory()->getLabel(),
        'Title' => $this->model->news_post->getTitle(),
        'Markdown' => $this->model->news_post->isMarkdown() ? ':white_check_mark:' : ':x:',
        'RSS exempt' => $this->model->news_post->isRSSExempt() ? ':white_check_mark:' : ':x:',
        'Authored by' => !\is_null($this->model->news_post->getUserId()) ? $this->model->news_post->getUser()->getAsMarkdown() : '*Anonymous*',
        'Edited by' => $this->model->active_user->getAsMarkdown(),
      ]);
      $desc = \substr($content, 0, \min(\BNETDocs\Libraries\Discord\Embed::MAX_DESCRIPTION - 13, \strlen($content) - 13));
      if (\strlen($desc) != \strlen($content)) $desc .= '…';
      if (!$this->model->news_post->isMarkdown()) $desc = '```' . \PHP_EOL . $desc . \PHP_EOL . '```';
      $embed->setDescription($desc);
      Logger::logToDiscord($event, $embed);
    }
  }
}
