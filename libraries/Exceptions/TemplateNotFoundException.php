<?php

namespace BNETDocs\Libraries\Exceptions;

use BNETDocs\Libraries\Exceptions\BNETDocsException;
use BNETDocs\Libraries\Template;

class TemplateNotFoundException extends BNETDocsException {

  public function __construct(Template &$template, $prev_ex = null) {
    parent::__construct("Unable to locate template required to load this view", 4, $prev_ex);
    if (extension_loaded("newrelic")) {
      newrelic_add_custom_parameter("template", $template->getTemplate());
    }
    $this->httpResponseCode = 500;
  }

}
