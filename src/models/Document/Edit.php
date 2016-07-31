<?php

namespace BNETDocs\Models\Document;

use \BNETDocs\Libraries\Model;

class Edit extends Model {

  public $acl_allowed;
  public $content;
  public $csrf_id;
  public $csrf_token;
  public $document;
  public $document_id;
  public $error;
  public $markdown;
  public $published;
  public $title;
  public $user;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->acl_allowed     = null;
    $this->content         = null;
    $this->csrf_id         = null;
    $this->csrf_token      = null;
    $this->document        = null;
    $this->document_id     = null;
    $this->error           = null;
    $this->markdown        = null;
    $this->published       = null;
    $this->title           = null;
    $this->user            = null;
    $this->user_session    = null;
  }

}
