<?php

namespace BNETDocs\Controllers;

class Maintenance extends Base
{
  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Maintenance();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args) : bool
  {
    $this->model->message = \array_shift($args);
    $this->model->_responseCode = 503;
    return true;
  }
}
