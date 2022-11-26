<?php

namespace BNETDocs\Controllers\Document;

class Index extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Document\Index();
  }

  public function invoke(?array $args): bool
  {
    $q = \BNETDocs\Libraries\Router::query();
    $this->model->order = $q['order'] ?? 'title-asc';

    switch ($this->model->order)
    {
      case 'created-asc': $order = ['created_datetime', 'ASC']; break;
      case 'created-desc': $order = ['created_datetime', 'DESC']; break;
      case 'id-asc': $order = ['id', 'ASC']; break;
      case 'id-desc': $order = ['id', 'DESC']; break;
      case 'title-asc': $order = ['title', 'ASC']; break;
      case 'title-desc': $order = ['title', 'DESC']; break;
      case 'updated-asc': $order = ['edited_datetime', 'ASC']; break;
      case 'updated-desc': $order = ['edited_datetime', 'DESC']; break;
      case 'user-id-asc': $order = ['user_id', 'ASC']; break;
      case 'user-id-desc': $order = ['user_id', 'DESC']; break;
      default: $order = null;
    }

    $acl = $this->model->active_user && $this->model->active_user->isStaff();
    $this->model->documents = \BNETDocs\Libraries\Document::getAllDocuments($order, !$acl);
    $this->model->sum_documents = count($this->model->documents);

    $this->model->_responseCode = 200;
    return true;
  }
}
