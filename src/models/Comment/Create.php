<?php

namespace BNETDocs\Models\Comment;

use \BNETDocs\Libraries\Model;

class Create extends Model {

  public $acl_allowed;
  public $origin;
  public $response;
  public $user;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->acl_allowed  = null;
    $this->origin       = null;
    $this->response     = null;
    $this->user         = null;
    $this->user_session = null;
  }

}
