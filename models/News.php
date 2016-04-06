<?php

namespace BNETDocs\Models;

use \BNETDocs\Libraries\Model;

class News extends Model {

  public $acl_allowed;
  public $news_posts;
  public $user;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->acl_allowed  = null;
    $this->news_posts   = [];
    $this->user         = null;
    $this->user_session = null;
  }

}
