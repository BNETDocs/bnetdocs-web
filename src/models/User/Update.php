<?php

namespace BNETDocs\Models\User;

use \CarlBennett\MVC\Libraries\Model;

class Update extends Model {

  public $display_name_1;
  public $display_name_2;
  public $display_name_error;

  public $email_1;
  public $email_2;
  public $email_error;

  public $username;
  public $username_error;
  public $username_max_len;

}
