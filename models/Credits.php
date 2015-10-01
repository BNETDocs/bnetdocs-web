<?php

namespace BNETDocs\Models;

use \BNETDocs\Libraries\Model;

class Credits extends Model {

  public $total_users;
  public $top_contributors_by_documents;
  public $top_contributors_by_news_posts;
  public $top_contributors_by_packets;
  public $top_contributors_by_servers;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->total_users                    = null;
    $this->top_contributors_by_documents  = null;
    $this->top_contributors_by_news_posts = null;
    $this->top_contributors_by_packets    = null;
    $this->top_contributors_by_servers    = null;
    $this->user_session                   = null;
  }

}
