<?php

namespace BNETDocs\Models\Packet;

use \BNETDocs\Libraries\Model;

class Popular extends Model {

  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->user_session = null;
  }

}
