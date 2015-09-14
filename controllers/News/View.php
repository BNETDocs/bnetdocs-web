<?php

namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\News\View as NewsViewModel;
use \BNETDocs\Views\News\ViewHtml as NewsViewHtmlView;

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
      default:
        throw new UnspecifiedViewException();
    }
    $model = new NewsViewModel();
    $this->getNewsPost($model);
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(300);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function getNewsPost(NewsViewModel &$model) {
    $model->news_post_id = $this->news_post_id;
    $model->news_post    = new NewsPost($this->news_post_id);
  }

}
