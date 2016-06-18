<?php

namespace BNETDocs\Models;

use \BNETDocs\Libraries\Model;

class Servers extends Model {

  public $server_types;
  public $servers;
  public $status_bitmasks;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->server_types    = null;
    $this->servers         = null;
    $this->status_bitmasks = null;
    $this->user_session    = null;
  }

}
