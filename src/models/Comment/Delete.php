<?php

namespace BNETDocs\Models\Comment;

use \BNETDocs\Libraries\Model;

class Delete extends Model {

  public $acl_allowed;
  public $comment;
  public $csrf_id;
  public $csrf_token;
  public $error;
  public $id;
  public $parent_id;
  public $parent_type;
  public $user;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->acl_allowed  = null;
    $this->comment      = null;
    $this->csrf_id      = null;
    $this->csrf_token   = null;
    $this->error        = null;
    $this->id           = null;
    $this->parent_id    = null;
    $this->parent_type  = null;
    $this->user         = null;
    $this->user_session = null;
  }

}
