<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Document\Sitemap as DocumentSitemapModel;
use \BNETDocs\Views\Document\SitemapHtml as DocumentSitemapHtmlView;
use \BNETDocs\Views\Document\SitemapJSON as DocumentSitemapJSONView;

class Sitemap extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new DocumentSitemapHtmlView();
      break;
      case "json":
        $view = new DocumentSitemapJSONView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new DocumentSitemapModel();
    $model->documents    = Document::getAllDocuments();
    $model->user_session = UserSession::load($router);
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
