<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Credits as CreditsLib;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Credits as CreditsModel;
use \BNETDocs\Views\CreditsHtml as CreditsHtmlView;

class Credits extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new CreditsHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new CreditsModel();
    $model->user_session = UserSession::load($router);
    $this->getCredits($model);
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function getCredits(CreditsModel &$model) {
    $credits = new CreditsLib();
    $model->total_users = CreditsLib::getTotalUsers();
    $model->top_contributors_by_documents
      = &$credits->getTopContributorsByDocuments();
    $model->top_contributors_by_news_posts
      = &$credits->getTopContributorsByNewsPosts();
    $model->top_contributors_by_packets
      = &$credits->getTopContributorsByPackets();
    $model->top_contributors_by_servers
      = &$credits->getTopContributorsByServers();
  }

}
