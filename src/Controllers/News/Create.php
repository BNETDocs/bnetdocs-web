<?php

namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\Core\HttpCode;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Models\News\Create as CreateModel;

class Create extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new CreateModel();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args): bool
  {
    $this->model->acl_allowed = $this->model->active_user
      && $this->model->active_user->getOption(\BNETDocs\Libraries\User\User::OPTION_ACL_NEWS_CREATE);

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = HttpCode::HTTP_FORBIDDEN;
      $this->model->error = $this->model->active_user ? CreateModel::ERROR_ACL_NOT_SET : CreateModel::ERROR_NOT_LOGGED_IN;
      return true;
    }

    $this->model->news_categories = \BNETDocs\Libraries\News\Category::getAll();
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

    $this->model->_responseCode = HttpCode::HTTP_OK;
    return true;
  }

  protected function handlePost(): void
  {
    $q = Router::query();
    $publish = (bool) ($q['publish'] ?? null);

    $this->model->category_id = (int) ($q['category'] ?? null);
    $this->model->content = $q['content'] ?? '';
    $this->model->markdown = (bool) ($q['markdown'] ?? null);
    $this->model->rss_exempt = (bool) ($q['rss_exempt'] ?? null);
    $this->model->title = $q['title'] ?? '';

    if (empty($this->model->title))
    {
      $this->model->error = CreateModel::ERROR_EMPTY_TITLE;
    }
    else if (empty($this->model->content))
    {
      $this->model->error = CreateModel::ERROR_EMPTY_CONTENT;
    }
    else
    {
      $this->model->news_post = new \BNETDocs\Libraries\News\Post(null);

      $this->model->news_post->setCategoryId($this->model->category_id);
      $this->model->news_post->setContent($this->model->content);
      $this->model->news_post->setMarkdown($this->model->markdown);
      $this->model->news_post->setPublished($publish);
      $this->model->news_post->setRSSExempt($this->model->rss_exempt);
      $this->model->news_post->setTitle($this->model->title);
      $this->model->news_post->setUserId($this->model->active_user->getId());

      $this->model->error = $this->model->news_post->commit() ? false : CreateModel::ERROR_INTERNAL;
    }

    if ($this->model->error !== false) return;

    $event = Logger::initEvent(
      \BNETDocs\Libraries\EventLog\EventTypes::NEWS_CREATED,
      $this->model->active_user,
      getenv('REMOTE_ADDR'),
      [
        'category_id' => $this->model->category_id,
        'content'     => $this->model->content,
        'error'       => $this->model->error,
        'markdown'    => $this->model->markdown,
        'published'   => $publish,
        'rss_exempt'  => $this->model->rss_exempt,
        'title'       => $this->model->title,
      ]
    );

    if ($event->commit())
    {
      $category_object = null;
      foreach ($this->model->news_categories as $news_categories_item)
      {
        if ($news_categories_item->getId() === $this->model->category_id)
        {
          $category_object = $news_categories_item;
          break;
        }
      }

      $embed = Logger::initDiscordEmbed($event, $this->model->news_post->getURI(), [
        'Category' => $category_object ? $category_object->getLabel() : '*null*',
        'Title' => $this->model->title,
        'Markdown' => $this->model->markdown ? ':white_check_mark:' : ':x:',
        'RSS exempt' => $this->model->rss_exempt ? ':white_check_mark:' : ':x:',
      ]);
      $desc = \substr($this->model->content, 0, \min(\BNETDocs\Libraries\Discord\Embed::MAX_DESCRIPTION - 13, \strlen($this->model->content) - 13));
      if (\strlen($desc) != \strlen($this->model->content)) $desc .= '…';
      if (!$this->model->markdown) $desc = '```' . \PHP_EOL . $desc . \PHP_EOL . '```';
      $embed->setDescription($desc);
      Logger::logToDiscord($event, $embed);
    }
  }
}
