<?php

namespace BNETDocs\Models\Comment;

use \CarlBennett\MVC\Libraries\Model;

class Edit extends Model {

  public $acl_allowed;
  public $comment;
  public $error;
  public $id;
  public $parent_id;
  public $parent_type;
  public $return_url;
  public $user;

}
