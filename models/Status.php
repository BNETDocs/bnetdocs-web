<?php

namespace BNETDocs\Models;

use \BNETDocs\Libraries\Model;

class Status extends Model {

  public $remote_address;
  public $remote_geoinfo;
  public $timestamp;
  public $timestamp_format;

  public function __construct() {
    $this->remote_address = getenv("REMOTE_ADDR");
    $this->remote_geoinfo = \geoip_record_by_name($this->remote_address);
    $this->timestamp = new \DateTime("now", new \DateTimeZone("UTC"));
    $this->timestamp_format = "Y-m-d H:i:s T";
  }

}
