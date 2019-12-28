<?php

namespace BNETDocs\Models\User;

use \CarlBennett\MVC\Libraries\Model;

class ChangePassword extends Model {

  public $csrf_id;
  public $csrf_token;
  public $error;
  public $error_extra;

}
