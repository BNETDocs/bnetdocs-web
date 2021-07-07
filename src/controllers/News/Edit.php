<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\NewsPostNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\NewsCategory;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\News\Edit as NewsEditModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;

class Edit extends Controller
{
  public function &run(Router &$router, View &$view, array &$args)
  {
    $data                   = $router->getRequestQueryArray();
    $model                  = new NewsEditModel();
    $model->active_user     = Authentication::$user;
    $model->category        = null;
    $model->content         = null;
    $model->error           = null;
    $model->markdown        = null;
    $model->news_categories = null;
    $model->news_post_id    = (isset($data['id']) ? $data['id'] : null);
    $model->news_post       = null;
    $model->published       = null;
    $model->rss_exempt      = null;
    $model->title           = null;

    $model->acl_allowed = ($model->active_user && $model->active_user->getAcl(
      User::OPTION_ACL_NEWS_MODIFY
    ));

    if (!$model->acl_allowed)
    {
      $model->_responseCode = 403;
      $model->error = 'ACL_NOT_SET';
      $view->render($model);
      return $model;
    }

    try { $model->news_post = new NewsPost($model->news_post_id); }
    catch (NewsPostNotFoundException $e) { $model->news_post = null; }
    catch (InvalidArgumentException $e) { $model->news_post = null; }

    if ($model->news_post === null) {
      $model->error = 'NOT_FOUND';
    } else {
      $flags = $model->news_post->getOptionsBitmask();

      $model->comments = Comment::getAll(
        Comment::PARENT_TYPE_NEWS_POST,
        $model->news_post_id
      );

      $model->news_categories = NewsCategory::getAll();
      usort($model->news_categories, function($a, $b){
        $oA = $a->getSortId();
        $oB = $b->getSortId();
        if ($oA == $oB) return 0;
        return ($oA < $oB) ? -1 : 1;
      });

      $model->category   = $model->news_post->getCategoryId();
      $model->content    = $model->news_post->getContent(false);
      $model->markdown   = ($flags & NewsPost::OPTION_MARKDOWN);
      $model->published  = ($flags & NewsPost::OPTION_PUBLISHED);
      $model->rss_exempt = ($flags & NewsPost::OPTION_RSS_EXEMPT);
      $model->title      = $model->news_post->getTitle();

      if ($router->getRequestMethod() == 'POST') {
        $this->handlePost($router, $model);
      }
    }

    $view->render($model);
    $model->_responseCode = ($model->acl_allowed ? 200 : 403);
    return $model;
  }

  protected function handlePost(Router &$router, NewsEditModel &$model)
  {
    if (!$model->acl_allowed)
    {
      $model->error = 'ACL_NOT_SET';
      return;
    }

    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $data       = $router->getRequestBodyArray();
    $category   = (isset($data['category'  ]) ? $data['category'  ] : null);
    $title      = (isset($data['title'     ]) ? $data['title'     ] : null);
    $markdown   = (isset($data['markdown'  ]) ? $data['markdown'  ] : null);
    $content    = (isset($data['content'   ]) ? $data['content'   ] : null);
    $rss_exempt = (isset($data['rss_exempt']) ? $data['rss_exempt'] : null);
    $publish    = (isset($data['publish'   ]) ? $data['publish'   ] : null);
    $save       = (isset($data['save'      ]) ? $data['save'      ] : null);

    $model->category   = $category;
    $model->title      = $title;
    $model->markdown   = $markdown;
    $model->content    = $content;
    $model->rss_exempt = $rss_exempt;

    $model->error = (empty($title) ? 'EMPTY_TITLE' : (empty($content) ? 'EMPTY_CONTENT' : null));

    if ($model->error) return;

    try
    {
      $model->news_post->setCategoryId($model->category);
      $model->news_post->setTitle($model->title);
      $model->news_post->setMarkdown($model->markdown);
      $model->news_post->setContent($model->content);
      $model->news_post->setRSSExempt($model->rss_exempt);
      $model->news_post->setPublished($publish);

      $model->news_post->setEditedCount(
        $model->news_post->getEditedCount() + 1
      );
      $model->news_post->setEditedDateTime(
        new DateTime( 'now', new DateTimeZone( 'Etc/UTC' ))
      );

      $success = $model->news_post->save();
      $model->error = false;
    }
    catch (QueryException $e)
    {
      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);

      $success = false;
      $model->error = 'INTERNAL_ERROR';
    }

    Logger::logEvent
    (
      EventTypes::NEWS_EDITED,
      ($model->active_user ? $model->active_user->getId() : null),
      getenv('REMOTE_ADDR'),
      json_encode
      ([
        'error'           => $model->error,
        'news_post_id'    => $model->news_post_id,
        'category_id'     => $model->news_post->getCategoryId(),
        'options_bitmask' => $model->news_post->getOptionsBitmask(),
        'title'           => $model->news_post->getTitle(),
        'content'         => $model->news_post->getContent(false),
      ])
    );
  }
}
