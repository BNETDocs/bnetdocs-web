<?php

namespace BNETDocs\Models\Server;

use \BNETDocs\Libraries\Model;

class View extends Model {

  public $server;
  public $server_id;
  public $server_type;

  public function __construct() {
    parent::__construct();
    $this->server        = null;
    $this->server_id     = null;
    $this->server_type   = null;
  }

}
