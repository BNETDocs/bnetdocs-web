<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Credits as CreditsLib;
use \BNETDocs\Models\Credits as CreditsModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Credits extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new CreditsModel();

    $this->getCredits($model);

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }

  protected function getCredits(CreditsModel &$model) {
    $credits = new CreditsLib();
    $model->total_users = CreditsLib::getTotalUsers();
    $model->top_contributors_by_documents
      = $credits->getTopContributorsByDocuments();
    $model->top_contributors_by_news_posts
      = $credits->getTopContributorsByNewsPosts();
    $model->top_contributors_by_packets
      = $credits->getTopContributorsByPackets();
    $model->top_contributors_by_servers
      = $credits->getTopContributorsByServers();
  }
}
