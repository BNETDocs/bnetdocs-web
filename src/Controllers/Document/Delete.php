<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Document\Delete as DeleteModel;

class Delete extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new DeleteModel();
  }

  public function invoke(?array $args): bool
  {
    $this->model->acl_allowed = $this->model->active_user
      && $this->model->active_user->getOption(\BNETDocs\Libraries\User::OPTION_ACL_DOCUMENT_DELETE);

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = 403;
      $this->model->error = DeleteModel::ERROR_ACCESS_DENIED;
      return true;
    }

    $q = Router::query();
    $this->model->id = isset($q['id']) ? (int) $q['id'] : null;

    try { if (!is_null($this->model->id)) $this->model->document = new \BNETDocs\Libraries\Document($this->model->id); }
    catch (\UnexpectedValueException) { $this->model->document = null; }

    if (!$this->model->document)
    {
      $this->model->_responseCode = 404;
      $this->model->error = DeleteModel::ERROR_NOT_FOUND;
      return true;
    }

    $this->model->title = $this->model->document->getTitle();

    if (Router::requestMethod() == Router::METHOD_POST)
    {
      $this->model->error = $this->model->document->deallocate() ? DeleteModel::ERROR_SUCCESS : DeleteModel::ERROR_INTERNAL;
  
      \BNETDocs\Libraries\EventLog\Event::log(
        \BNETDocs\Libraries\EventLog\EventTypes::DOCUMENT_DELETED,
        $this->model->active_user,
        getenv('REMOTE_ADDR'),
        [
          'error' => $this->model->error,
          'document' => $this->model->document,
        ]
      );
    }

    $this->model->_responseCode = 200;
    return true;
  }
}
