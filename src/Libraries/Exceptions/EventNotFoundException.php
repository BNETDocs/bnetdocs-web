<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \BNETDocs\Libraries\Logger;
use \Exception;

class EventNotFoundException extends BNETDocsException {

  public function __construct($id, Exception &$prev_ex = null) {
    parent::__construct('Event not found', 19, $prev_ex);
    Logger::logMetric('event_id', $id);
  }

}
