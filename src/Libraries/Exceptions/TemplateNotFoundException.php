<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Template;
use \Exception;

class TemplateNotFoundException extends BNETDocsException {

  public function __construct(Template &$template, Exception &$prev_ex = null) {
    parent::__construct(
      "Unable to locate template required to load this view", 5, $prev_ex
    );
  }

}
