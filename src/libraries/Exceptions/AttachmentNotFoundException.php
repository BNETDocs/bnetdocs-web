<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \BNETDocs\Libraries\Logger;
use \Exception;

class AttachmentNotFoundException extends BNETDocsException {

  public function __construct($query, Exception &$prev_ex = null) {
    parent::__construct("Attachment not found", 22, $prev_ex);
    Logger::logMetric("query", $query);
  }

}
