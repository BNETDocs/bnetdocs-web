<?php

namespace BNETDocs\Models\Document;

use \BNETDocs\Libraries\Model;

class Index extends Model {

  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->documents     = null;
    $this->sum_documents = null;
    $this->user_session  = null;
  }

}
