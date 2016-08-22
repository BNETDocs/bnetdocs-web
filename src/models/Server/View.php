<?php

namespace BNETDocs\Models\Server;

use \BNETDocs\Libraries\Model;

class View extends Model {

  public $server;
  public $server_id;
  public $server_response_time;
  public $server_type;
  public $server_uptime;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->server               = null;
    $this->server_id            = null;
    $this->server_response_time = null;
    $this->server_type          = null;
    $this->server_uptime        = null;
    $this->user_session         = null;
  }

}
