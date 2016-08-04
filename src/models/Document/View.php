<?php

namespace BNETDocs\Models\Document;

use \BNETDocs\Libraries\Model;

class View extends Model {

  public $acl_allowed;
  public $attachments;
  public $comments;
  public $document;
  public $document_id;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->acl_allowed  = null;
    $this->attachments  = null;
    $this->comments     = null;
    $this->document     = null;
    $this->document_id  = null;
    $this->user_session = null;
  }

}
