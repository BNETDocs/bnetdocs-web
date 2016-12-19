<?php

namespace BNETDocs\Models\Document;

use \CarlBennett\MVC\Libraries\Model;

class Delete extends Model {

  public $acl_allowed;
  public $csrf_id;
  public $csrf_token;
  public $document;
  public $error;
  public $id;
  public $title;
  public $user;

}
