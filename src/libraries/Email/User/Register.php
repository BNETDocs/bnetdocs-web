<?php

namespace BNETDocs\Libraries\Email\User;

use \BNETDocs\Libraries\EmailMessage;
use \BNETDocs\Libraries\Pair;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\User;
use \StdClass;

class Register extends EmailMessage {

  protected $token;
  protected $user;

  public function __construct(User &$user, $token) {
    parent::__construct();
    $this->token = $token;
    $this->user  = $user;
  }

  public function build() {
    $context        = new StdClass();
    $context->user  = $user;
    $context->token = $token;

    ob_start(); (new Template(
      $context, "Email/User/Register.rich"
    ))->render(); $rich = ob_get_clean();

    ob_start(); (new Template(
      $context, "Email/User/Register.plain"
    ))->render(); $plain = ob_get_clean();

    $parts = [
      new Pair("text/html;charset=utf-8", $rich),
      new Pair("text/plain;charset=utf-8", $plain)
    ];

    $this->setMultiPartBody($parts);

    return true;
  }

}
