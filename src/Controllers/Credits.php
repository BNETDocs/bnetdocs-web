<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Credits as CreditsLib;

class Credits extends Base
{
  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Credits();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args): bool
  {
    $this->model->_responseCode = 200;
    $this->model->top_contributors_by_comments = CreditsLib::getTopContributorsByComments();
    $this->model->top_contributors_by_documents = CreditsLib::getTopContributorsByDocuments();
    $this->model->top_contributors_by_news_posts = CreditsLib::getTopContributorsByNewsPosts();
    $this->model->top_contributors_by_packets = CreditsLib::getTopContributorsByPackets();
    $this->model->top_contributors_by_servers = CreditsLib::getTopContributorsByServers();
    $this->model->total_users = CreditsLib::getTotalUsers();
    return true;
  }
}
