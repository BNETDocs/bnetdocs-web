<?php

namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\Attachment;
use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Exceptions\NewsPostNotFoundException;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\User;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\News\View as NewsViewModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class View extends Controller {

  protected $news_post_id;

  public function __construct($news_post_id) {
    parent::__construct();
    $this->news_post_id = $news_post_id;
  }

  public function &run(Router &$router, View &$view, array &$args) {

    $model = new NewsViewModel();
    $model->user_session = UserSession::load($router);
    $model->user         = (isset($model->user_session) ?
                            new User($model->user_session->user_id) :
                            null
                           );

    $model->acl_allowed = ($model->user &&
      $model->user->getOptionsBitmask() & (
        User::OPTION_ACL_NEWS_CREATE |
        User::OPTION_ACL_NEWS_MODIFY |
        User::OPTION_ACL_NEWS_DELETE
      )
    );

    $this->getNewsPost($model);

    $view->render($model);

    $model->_responseCode = ($model->news_post ? 200 : 404);
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

  protected function getNewsPost(NewsViewModel &$model) {
    $model->news_post_id = (int) $this->news_post_id;
    try {
      $model->news_post = new NewsPost($this->news_post_id);
    } catch (NewsPostNotFoundException $e) {
      $model->news_post = null;
    }

    // Don't show unpublished news posts to non-staff
    if ($model->news_post
      && !($model->news_post->getOptionsBitmask() & NewsPost::OPTION_PUBLISHED)
      && !$model->acl_allowed) $model->news_post = null;

    // Load attachments and comments
    if ($model->news_post) {
      $model->attachments = Attachment::getAll(
        Comment::PARENT_TYPE_NEWS_POST,
        $model->news_post_id
      );
      $model->comments = Comment::getAll(
        Comment::PARENT_TYPE_NEWS_POST,
        $model->news_post_id
      );
    }
  }

}
