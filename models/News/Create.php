<?php

namespace BNETDocs\Models\News;

use \BNETDocs\Libraries\Model;

class Create extends Model {

  public $acl_allowed;
  public $category;
  public $content;
  public $csrf_id;
  public $csrf_token;
  public $error;
  public $markdown;
  public $news_categories;
  public $title;
  public $user;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->acl_allowed     = null;
    $this->category        = null;
    $this->content         = null;
    $this->csrf_id         = null;
    $this->csrf_token      = null;
    $this->error           = null;
    $this->markdown        = null;
    $this->news_categories = null;
    $this->title           = null;
    $this->user            = null;
    $this->user_session    = null;
  }

}
