<?php

namespace BNETDocs\Models\Document;

use \BNETDocs\Libraries\Model;

class View extends Model {

  public $document;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->document     = null;
    $this->user_session = null;
  }

}
