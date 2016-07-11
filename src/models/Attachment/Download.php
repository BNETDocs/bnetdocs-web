<?php

namespace BNETDocs\Models\Attachment;

use \BNETDocs\Libraries\Model;

class Download extends Model {

  public $attachment;
  public $attachment_id;
  public $extra_headers;

  public function __construct() {
    parent::__construct();
    $this->attachment    = null;
    $this->attachment_id = null;
    $this->extra_headers = null;
  }

}
