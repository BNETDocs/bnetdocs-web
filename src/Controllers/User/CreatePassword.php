<?php

namespace BNETDocs\Controllers\User;

class CreatePassword extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\User\CreatePassword();
  }

  public function invoke(?array $args): bool
  {
    $q = \BNETDocs\Libraries\Router::query();
    $this->model->_responseCode = 200;
    $this->model->input = $q['input'] ?? null;
    $this->model->output = empty($this->model->input) ? null : \BNETDocs\Libraries\User::createPassword($this->model->input);
    return true;
  }
}
