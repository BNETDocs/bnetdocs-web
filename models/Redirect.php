<?php

namespace BNETDocs\Models;

use \BNETDocs\Libraries\Model;

class Redirect extends Model {

  public $redirect_code;
  public $redirect_to;

  public function __construct($redirect_code, $redirect_to) {
    $this->redirect_code = $redirect_code;
    $this->redirect_to   = $redirect_to;
  }

}
