<?php

namespace BNETDocs\Models\EventLog;

use \BNETDocs\Libraries\Model;

class Index extends Model {

  public $acl_allowed;
  public $event_log;
  public $sum_event_log;
  public $user;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->acl_allowed   = null;
    $this->event_log     = null;
    $this->sum_event_log = null;
    $this->user          = null;
    $this->user_session  = null;
  }

}
