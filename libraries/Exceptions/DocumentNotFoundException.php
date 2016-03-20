<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \BNETDocs\Libraries\Logger;
use \Exception;

class DocumentNotFoundException extends BNETDocsException {

  public function __construct($query, Exception &$prev_ex = null) {
    parent::__construct("Document not found", 14, $prev_ex);
    Logger::logMetric("query", $query);
  }

}
