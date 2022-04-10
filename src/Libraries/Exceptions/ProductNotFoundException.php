<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \BNETDocs\Libraries\Logger;
use \Exception;

class ProductNotFoundException extends BNETDocsException {

  public function __construct($query, Exception &$prev_ex = null) {
    parent::__construct("Product not found", 17, $prev_ex);
    Logger::logMetric("query", $query);
  }

}
