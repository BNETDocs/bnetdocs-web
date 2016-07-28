<?php

namespace BNETDocs\Models\Comment;

use \BNETDocs\Libraries\Model;

class Create extends Model {

  public $origin;
  public $response;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->origin       = null;
    $this->response     = null;
    $this->user_session = null;
  }

}
