<?php

namespace BNETDocs\Models\News;

use \BNETDocs\Libraries\Model;

class Delete extends Model {

  public $acl_allowed;
  public $csrf_id;
  public $csrf_token;
  public $error;
  public $id;
  public $news_post;
  public $title;
  public $user;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->acl_allowed  = null;
    $this->csrf_id      = null;
    $this->csrf_token   = null;
    $this->error        = null;
    $this->id           = null;
    $this->news_post    = null;
    $this->title        = null;
    $this->user         = null;
    $this->user_session = null;
  }

}
