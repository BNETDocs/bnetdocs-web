<?php

namespace BNETDocs\Controllers;

class PrivacyNotice extends Base
{
  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\PrivacyNotice();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args): bool
  {
    $privacy = &\CarlBennett\MVC\Libraries\Common::$config->bnetdocs->privacy;
    $this->model->data_location = $privacy->data_location;
    $this->model->email_domain = $privacy->contact->email_domain;
    $this->model->email_mailbox = $privacy->contact->email_mailbox;
    $this->model->organization = $privacy->organization;
    $this->model->_responseCode = 200;
    return true;
  }
}
