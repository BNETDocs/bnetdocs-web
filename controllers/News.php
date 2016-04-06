<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\User;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\News as NewsModel;
use \BNETDocs\Views\NewsHtml as NewsHtmlView;
use \BNETDocs\Views\NewsRSS as NewsRSSView;
use \DateTime;
use \DateTimeZone;

class News extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new NewsHtmlView();
      break;
      case "rss":
        $view = new NewsRSSView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new NewsModel();
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
    $this->getNews($model);
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function getNews(NewsModel &$model) {
    $model->news_posts = NewsPost::getAllNews(true);

    // Remove news posts that are not published
    if (!$model->acl_allowed && $model->news_posts) {
      $i = count($model->news_posts) - 1;
      while ($i >= 0) {
        if (!($model->news_posts[$i]->getOptionsBitmask()
          & NewsPost::OPTION_PUBLISHED)) {
          unset($model->news_posts[$i]);
        }
        --$i;
      }
    }
  }

}
