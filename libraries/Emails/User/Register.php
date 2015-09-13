<?php

namespace BNETDocs\Libraries\Emails\User;

use \BNETDocs\Libraries\EmailMessage;
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

  public function build($plaintext) {
    $context = new StdClass();
    $context->user  = $user;
    $context->token = $token;
    $this->setBody(
      (new Template(
        $context,
        "User/Register." . ($plaintext ? "plain" : "rich"),
        "/email_templates"
      ))->render()
    );
  }

}
