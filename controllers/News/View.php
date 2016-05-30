<?php

namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\NewsPostNotFoundException;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\User;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\News\View as NewsViewModel;
use \BNETDocs\Views\News\ViewHtml as NewsViewHtmlView;
use \BNETDocs\Views\News\ViewPlain as NewsViewPlainView;

class View extends Controller {

  protected $news_post_id;

  public function __construct($news_post_id) {
    parent::__construct();
    $this->news_post_id = $news_post_id;
  }

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new NewsViewHtmlView();
      break;
      case "txt":
        $view = new NewsViewPlainView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new NewsViewModel();
    $model->user_session = UserSession::load($router);
    $model->user         = (isset($model->user_session) ?
                            new User($model->user_session->user_id) :
                            null);
    $model->acl_allowed  = ($model->user &&
      $model->user->getOptionsBitmask() & (
        User::OPTION_ACL_NEWS_CREATE |
        User::OPTION_ACL_NEWS_MODIFY |
        User::OPTION_ACL_NEWS_DELETE
      )
    );
    $this->getNewsPost($model);
    ob_start();
    $view->render($model);
    $router->setResponseCode(($model->news_post ? 200 : 404));
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function getNewsPost(NewsViewModel &$model) {
    $model->news_post_id = $this->news_post_id;
    try {
      $model->news_post = new NewsPost($this->news_post_id);
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
