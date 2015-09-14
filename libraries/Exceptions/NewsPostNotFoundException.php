<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \Exception;

class NewsPostNotFoundException extends BNETDocsException {

  public function __construct($query, Exception &$prev_ex = null) {
    parent::__construct("News post not found", 8, $prev_ex);
  }

}
