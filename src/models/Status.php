<?php

namespace BNETDocs\Models;

use \CarlBennett\MVC\Libraries\Model;
use \DateTimeInterface;
use \JsonSerializable;

class Status extends Model {

  public $healthcheck;
  public $remote_address;
  public $remote_geoinfo;
  public $timestamp;
  public $version_info;

}
