<?php

namespace BNETDocs\Models\User;

use \BNETDocs\Libraries\Model;

class View extends Model {

  public $sum_documents;
  public $sum_news_posts;
  public $sum_packets;
  public $sum_servers;
  public $user;
  public $user_est;
  public $user_id;
  public $user_profile;

  public function __construct() {
    parent::__construct();
    $this->sum_documents  = null;
    $this->sum_news_posts = null;
    $this->sum_packets    = null;
    $this->sum_servers    = null;
    $this->user           = null;
    $this->user_est       = null;
    $this->user_id        = null;
    $this->user_profile   = null;
  }

}
