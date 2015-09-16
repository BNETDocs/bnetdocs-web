<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\Router;
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
    $this->getNews($model);
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(300);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function getNews(NewsModel &$model) {
    $model->news_posts = NewsPost::getAllNews(true);
  }

}
