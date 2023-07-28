<?php

namespace BNETDocs\Controllers\Server;

class View extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Server\View();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args): bool
  {
    try { $this->model->server = new \BNETDocs\Libraries\Server((int) \array_shift($args)); }
    catch (\UnexpectedValueException) { $this->model->server = null; }

    $this->model->_responseCode = $this->model->server ? 200 : 404;
    return true;
  }
}
