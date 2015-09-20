<?php

namespace BNETDocs\Models;

use \BNETDocs\Libraries\Model;

class Servers extends Model {

  public $servers;
  public $server_types;

  public function __construct() {
    parent::__construct();
    $this->servers      = null;
    $this->server_types = null;
  }

}
