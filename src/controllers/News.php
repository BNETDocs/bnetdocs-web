<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\Pagination;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\News as NewsModel;
use \BNETDocs\Views\NewsRSS as NewsRSSView;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \DateTime;
use \DateTimeZone;
use \OutOfBoundsException;

class News extends Controller {

  const NEWS_PER_PAGE = 5;

  public function &run(Router &$router, View &$view, array &$args) {

    $model = new NewsModel();

    $model->user = Authentication::$user;

    $model->acl_allowed = ($model->user && $model->user->getAcl(
      User::OPTION_ACL_NEWS_CREATE |
      User::OPTION_ACL_NEWS_MODIFY |
      User::OPTION_ACL_NEWS_DELETE
    ));

    $query = $router->getRequestQueryArray();
    $page  = (isset($query["page"]) ? ((int) $query["page"]) - 1 : null);

    $this->getNews(
      $model, ($view instanceof NewsRSSView),
      (!$view instanceof NewsRSSView), $page
    );

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

  protected function getNews(NewsModel &$model, $rss, $paginate, $page) {
    $model->news_posts = NewsPost::getAllNews(true);

    // Remove news posts that are not published or are RSS exempt
    if ($model->news_posts) {
      $i = count($model->news_posts) - 1;
      while ($i >= 0) {
        if ((!$model->acl_allowed && !$model->news_posts[$i]->getPublished())
          || ($rss && $model->news_posts[$i]->getRSSExempt())) {
          unset($model->news_posts[$i]);
        }
        --$i;
      }
    }

    if ($paginate) {
      try {
        $model->pagination = new Pagination(
          $model->news_posts, $page, self::NEWS_PER_PAGE
        );
        $model->news_posts = $model->pagination->getPage();
      } catch (OutOfBoundsException $e) {
        $model->news_posts = null;
      }
    } else {
      $model->pagination = null;
    }

  }

}
