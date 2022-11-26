<?php

namespace BNETDocs\Controllers\User;

class Verify extends \BNETDocs\Controllers\Base
{
  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\User\Verify();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args) : bool
  {
    $q = \BNETDocs\Libraries\Router::query();
    $this->model->token = $q['t'] ?? null;
    $this->model->user_id = $q['u'] ?? null;

    if (is_numeric($this->model->user_id))
    {
      try { $this->model->user = new \BNETDocs\Libraries\User((int) $this->model->user_id); }
      catch (\UnexpectedValueException) { $this->model->user = null; }
    }

    $user_token = $this->model->user ? $this->model->user->getVerifierToken() : null;
    if (!$this->model->user || $user_token !== $this->model->token)
    {
      $this->model->error = 'INVALID_TOKEN';
      $this->model->_responseCode = 400;
      return true;
    }

    try
    {
      $this->model->user->setVerified(true, true);
      $this->model->user->commit();
      $this->model->error = false;
    }
    catch (\Throwable) { $this->model->error = 'INTERNAL_ERROR'; }

    if (!$this->model->error)
      \BNETDocs\Libraries\Logger::logEvent(
        \BNETDocs\Libraries\EventTypes::USER_VERIFIED,
        $this->model->user_id,
        getenv('REMOTE_ADDR'),
        json_encode(['error' => $this->model->error])
      );

    $this->model->_responseCode = 200;
    return true;
  }
}
