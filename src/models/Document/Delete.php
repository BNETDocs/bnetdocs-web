<?php

namespace BNETDocs\Models\Document;

use \BNETDocs\Libraries\Model;

class Delete extends Model {

  public $acl_allowed;
  public $csrf_id;
  public $csrf_token;
  public $document;
  public $error;
  public $id;
  public $title;
  public $user;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->acl_allowed  = null;
    $this->csrf_id      = null;
    $this->csrf_token   = null;
    $this->document     = null;
    $this->error        = null;
    $this->id           = null;
    $this->title        = null;
    $this->user         = null;
    $this->user_session = null;
  }

}
