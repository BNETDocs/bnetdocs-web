<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Libraries\Router;

class Create extends \BNETDocs\Controllers\Base
{
  public const EMPTY_CONTENT = 'EMPTY_CONTENT';
  public const EMPTY_TITLE = 'EMPTY_TITLE';
  public const INTERNAL_ERROR = 'INTERNAL_ERROR';

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

  protected function handlePost(): void
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
      $this->model->error = self::EMPTY_TITLE;
    } else if (empty($content)) {
      $this->model->error = self::EMPTY_CONTENT;
    }

    $document = new \BNETDocs\Libraries\Document(null);
    $document->setBrief($brief);
    $document->setContent($content);
    $document->setMarkdown($markdown);
    $document->setPublished($publish);
    $document->setTitle($title);
    $document->setUser($this->model->active_user);

    if (!$document->commit())
    {
      $this->model->error = self::INTERNAL_ERROR;
      return;
    }
    $this->model->error = false;

    $event = Logger::initEvent(
      \BNETDocs\Libraries\EventLog\EventTypes::DOCUMENT_CREATED,
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

    if ($event->commit())
    {
      $embed = Logger::initDiscordEmbed($event, $document->getURI(), [
        'Title' => $title,
        'Brief' => $brief,
        'Markdown' => $markdown ? ':white_check_mark:' : ':x:',
      ]);
      $embed->setDescription($markdown ? $content : '```' . \PHP_EOL . $content . \PHP_EOL . '```');
      Logger::logToDiscord($event, $embed);
    }
  }
}
