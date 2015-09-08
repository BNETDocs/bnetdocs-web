<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Markdown;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\User;
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
    $md = new Markdown();
    $user = new User(63);
    $news_posts = Common::$cache->get("bnetdocs-newsposts");
    $model->cache_date = new DateTime();
    $model->cache_date->setTimezone(new DateTimeZone("GMT"));
    if ($news_posts !== false) {
      $model->news_posts = unserialize($news_posts);
    } else {
      $model->news_posts = [
        1 => [
          "id" => 1,
          "author" => $user->getName(),
          "timestamp_published" => new DateTime("2015-09-03 04:46:00 CDT"),
          "title" => "Missing pages added",
          "content" => $md->text("The missing pages everywhere on this site have been created with the content of not yet implemented. Building out these pages will take some time, but they will at least no longer show up as 404 Not Found.\n\nAnd yes, this project is still getting some life put into it, just in the background scenes that aren't very visible to the end user such as yourself.")
        ],
        0 => [
          "id" => 0,
          "author" => $user->getName(),
          "timestamp_published" => new DateTime("2015-05-25 21:53:00 CDT"),
          "title" => "Work in progress",
          "content" => $md->text("I've been giving life back into BNETDocs: Phoenix recently. There's been lots of changes to the code repository and restructuring it. There's been lots of new designs and paradigms put in place that are better than the previous Phoenix from last year. More news coming soon.")
        ]
      ];
      Common::$cache->set("bnetdocs-newsposts", serialize($model->news_posts), 300);
    }
  }

}
