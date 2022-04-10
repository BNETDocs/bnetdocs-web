<?php

namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Exceptions\NewsPostNotFoundException;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\News\View as NewsViewModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View as ViewLib;

class View extends Controller {
  public function &run(Router &$router, ViewLib &$view, array &$args) {
    $model = new NewsViewModel();
    $model->active_user = Authentication::$user;
    $model->news_post_id = array_shift($args);

    $model->acl_allowed = ($model->active_user && $model->active_user->getOption(
      User::OPTION_ACL_NEWS_CREATE |
      User::OPTION_ACL_NEWS_MODIFY |
      User::OPTION_ACL_NEWS_DELETE
    ));

    $this->getNewsPost($model);

    $view->render($model);
    $model->_responseCode = ($model->news_post ? 200 : 404);
    return $model;
  }

  protected function getNewsPost(NewsViewModel &$model) {
    $model->news_post_id = (int) $model->news_post_id;
    try {
      $model->news_post = new NewsPost($model->news_post_id);
    } catch (NewsPostNotFoundException $e) {
      $model->news_post = null;
    }

    // Don't show unpublished news posts to non-staff
    if ($model->news_post
      && !($model->news_post->getOptionsBitmask() & NewsPost::OPTION_PUBLISHED)
      && !$model->acl_allowed) $model->news_post = null;

    // Load comments
    if ($model->news_post) {
      $model->comments = Comment::getAll(
        Comment::PARENT_TYPE_NEWS_POST,
        $model->news_post_id
      );
    }
  }
}
