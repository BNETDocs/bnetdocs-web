<?php

namespace BNETDocs\Models\News;

use \BNETDocs\Libraries\Model;

class View extends Model {

  public $acl_allowed;
  public $attachments;
  public $comments;
  public $news_post;
  public $news_post_id;
  public $user;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->acl_allowed  = null;
    $this->attachments  = null;
    $this->comments     = null;
    $this->news_post    = null;
    $this->news_post_id = null;
    $this->user         = null;
    $this->user_session = null;
  }

}
