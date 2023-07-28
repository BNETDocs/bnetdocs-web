<?php

namespace BNETDocs\Controllers;

class Robotstxt extends Base
{
  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Robotstxt();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args): bool
  {
    $this->model->rules = \CarlBennett\MVC\Libraries\Common::$config->bnetdocs->robotstxt;
    $this->model->_responseCode = 200;
    return true;
  }
}
