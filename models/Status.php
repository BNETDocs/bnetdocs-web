<?php

namespace BNETDocs\Models;

use \BNETDocs\Libraries\Model;

class Status extends Model {

  public $remote_address;
  public $remote_geoinfo;
  public $timestamp;
  public $version_info;

  public function __construct() {
    parent::__construct();
    $this->remote_address   = null;
    $this->remote_geoinfo   = null;
    $this->timestamp        = null;
    $this->version_info     = null;
  }

}
