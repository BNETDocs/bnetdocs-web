<?php

namespace BNETDocs\Models\API;

use \BNETDocs\Libraries\Model;

class Comment extends Model {

  public $response;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->response     = null;
    $this->user_session = null;
  }

}
