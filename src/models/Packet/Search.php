<?php

namespace BNETDocs\Models\Packet;

use \BNETDocs\Libraries\Model;

class Search extends Model {

  public $packets;
  public $query;
  public $sum_packets;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->packets      = null;
    $this->query        = null;
    $this->sum_packets  = null;
    $this->user_session = null;
  }

}
