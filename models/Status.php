<?php

namespace BNETDocs\Models;

use \BNETDocs\Libraries\Model;
use \DateTime;
use \DateTimeZone;

class Status extends Model {

  public $remote_address;
  public $remote_geoinfo;
  public $timestamp;
  public $timestamp_format;
  public $version_info;

  public function __construct() {
    parent::__construct();
    $this->remote_address   = getenv("REMOTE_ADDR");
    $this->remote_geoinfo   = geoip_record_by_name($this->remote_address);
    $this->timestamp        = new DateTime("now", new DateTimeZone("UTC"));
    $this->timestamp_format = "Y-m-d H:i:s T";
    $this->version_info     = Common::versionProperties();
  }

}
