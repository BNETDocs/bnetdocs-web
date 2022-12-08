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

    $orderMap = [ // This code was refactored by OpenAI
      'created-asc' => ['created_datetime', 'ASC'],
      'created-desc' => ['created_datetime', 'DESC'],
      'id-asc' => ['id', 'ASC'],
      'id-desc' => ['id', 'DESC'],
      'title-asc' => ['title', 'ASC'],
      'title-desc' => ['title', 'DESC'],
      'updated-asc' => ['edited_datetime', 'ASC'],
      'updated-desc' => ['edited_datetime', 'DESC'],
      'user-id-asc' => ['user_id', 'ASC'],
      'user-id-desc' => ['user_id', 'DESC'],
    ];

    $order = $orderMap[$this->model->order] ?? null;

    $acl = $this->model->active_user && $this->model->active_user->isStaff();
    $this->model->documents = \BNETDocs\Libraries\Document::getAllDocuments($order, !$acl);
    $this->model->sum_documents = count($this->model->documents);

    $this->model->_responseCode = 200;
    return true;
  }
}
