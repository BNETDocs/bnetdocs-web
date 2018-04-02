<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \BNETDocs\Libraries\Logger;
use \Exception;

class TagNotFoundException extends BNETDocsException {

  public function __construct( $id, Exception &$prev_ex = null ) {
    parent::__construct( 'Tag not found', 23, $prev_ex );
    Logger::logMetric( 'tag_id', $id );
  }

}
