<?php

namespace BNETDocs\Models\Packet;

use \BNETDocs\Libraries\Model;

class View extends Model {

  public $comments;
  public $packet;
  public $packet_id;
  public $used_by;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->comments     = null;
    $this->packet       = null;
    $this->packet_id    = null;
    $this->used_by      = null;
    $this->user_session = null;
  }

}
