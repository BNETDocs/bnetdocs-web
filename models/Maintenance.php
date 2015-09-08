<?php

namespace BNETDocs\Models;

use \BNETDocs\Libraries\Model;

class Maintenance extends Model {

  public $message;

  public function __construct() {
    parent::__construct();
    $this->message = null;
  }

}
