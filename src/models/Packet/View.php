<?php

namespace BNETDocs\Models\Packet;

use \BNETDocs\Libraries\Model;

class View extends Model {

  public $packet;
  public $used_by;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->packet       = null;
    $this->used_by      = null;
    $this->user_session = null;
  }

}
