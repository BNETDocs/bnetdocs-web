<?php

namespace BNETDocs\Models\News;

use \BNETDocs\Libraries\Model;

class Create extends Model {

  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->user_session = null;
  }

}
