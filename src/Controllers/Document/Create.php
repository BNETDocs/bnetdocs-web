<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\Router;

class Create extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Document\Create();
  }

  public function invoke(?array $args): bool
  {
    $this->model->acl_allowed = $this->model->active_user
      && $this->model->active_user->getOption(\BNETDocs\Libraries\User::OPTION_ACL_DOCUMENT_CREATE);

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = 403;
      $this->model->error = 'ACL_NOT_SET';
      return true;
    }

    if (Router::requestMethod() == Router::METHOD_POST)
      $this->handlePost();
    else if (Router::requestMethod() == Router::METHOD_GET)
      $this->model->markdown = true;

    $this->model->_responseCode = 200;
    return true;
  }

  protected function handlePost() : void
  {
    $data = Router::query();
    $title = $data['title'] ?? null;
    $markdown = $data['markdown'] ?? null;
    $brief = $data['brief'] ?? null;
    $content = $data['content'] ?? null;
    $publish = $data['publish'] ?? null;

    $markdown = ($markdown ? true : false);
    $publish = ($publish ? true : false);

    $this->model->title = $title;
    $this->model->brief = $brief;
    $this->model->markdown = $markdown;
    $this->model->content = $content;

    if (empty($title)) {
      $this->model->error = 'EMPTY_TITLE';
    } else if (empty($content)) {
      $this->model->error = 'EMPTY_CONTENT';
    }

    $document = new \BNETDocs\Libraries\Document(null);
    $document->setBrief($brief);
    $document->setContent($content);
    $document->setMarkdown($markdown);
    $document->setPublished($publish);
    $document->setTitle($title);
    $document->setUser($this->model->active_user);
    $this->model->error = $document->commit() ? false : 'INTERNAL_ERROR';

    if ($this->model->error === false)
      \BNETDocs\Libraries\Event::log(
        \BNETDocs\Libraries\EventTypes::DOCUMENT_CREATED,
        $this->model->active_user,
        getenv('REMOTE_ADDR'),
        [
          'brief'     => $brief,
          'content'   => $content,
          'error'     => $this->model->error,
          'markdown'  => $markdown,
          'published' => $publish,
          'title'     => $title,
        ]
      );
  }
}
