<?php

namespace BNETDocs\Models\Packet;

use \BNETDocs\Libraries\Model;

class Index extends Model {

  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->packets      = null;
    $this->sum_packets  = null;
    $this->user_session = null;
  }

}
