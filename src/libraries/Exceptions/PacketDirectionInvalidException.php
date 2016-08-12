<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \BNETDocs\Libraries\Logger;
use \Exception;

class PacketDirectionInvalidException extends BNETDocsException {

  public function __construct($query, Exception &$prev_ex = null) {
    parent::__construct("Packet direction is invalid", 19, $prev_ex);
    Logger::logMetric("query", $query);
  }

}
