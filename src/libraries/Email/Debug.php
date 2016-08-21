<?php

namespace BNETDocs\Libraries\Email;

use \BNETDocs\Libraries\EmailMessage;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Pair;
use \CarlBennett\MVC\Libraries\Template;
use \StdClass;

class Debug extends EmailMessage {

  public function build() {
    $context = new StdClass();

    ob_start(); (new Template(
      $context, "Email/Debug.rich"
    ))->render(); $rich = ob_get_clean();

    ob_start(); (new Template(
      $context, "Email/Debug.plain"
    ))->render(); $plain = ob_get_clean();

    $parts = [
      new Pair("text/html;charset=utf-8", $rich),
      new Pair("text/plain;charset=utf-8", $plain)
    ];

    $this->setMultiPartBody($parts);

    return true;
  }

}
