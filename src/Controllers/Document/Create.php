<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\Core\HttpCode;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Models\Document\Create as CreateModel;

class Create extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new CreateModel();
  }

  public function invoke(?array $args): bool
  {
    $this->model->acl_allowed = $this->model->active_user
      && $this->model->active_user->getOption(\BNETDocs\Libraries\User\User::OPTION_ACL_DOCUMENT_CREATE);

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = HttpCode::HTTP_FORBIDDEN;
      $this->model->error = $this->model->active_user ? CreateModel::ERROR_ACL_NOT_SET : CreateModel::ERROR_NOT_LOGGED_IN;
      return true;
    }

    if (Router::requestMethod() == Router::METHOD_POST)
      $this->handlePost();
    else if (Router::requestMethod() == Router::METHOD_GET)
      $this->model->markdown = true;

    $this->model->_responseCode = HttpCode::HTTP_OK;
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

    if (empty($title))
    {
      $this->model->error = CreateModel::ERROR_EMPTY_TITLE;
    }
    else if (empty($content))
    {
      $this->model->error = CreateModel::ERROR_EMPTY_CONTENT;
    }

    if ($this->model->error) return;

    $document = new \BNETDocs\Libraries\Document(null);
    $document->setBrief($brief);
    $document->setContent($content);
    $document->setMarkdown($markdown);
    $document->setPublished($publish);
    $document->setTitle($title);
    $document->setUser($this->model->active_user);

    if (!$document->commit())
    {
      $this->model->error = CreateModel::ERROR_INTERNAL;
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
      if (empty($brief)) $brief = '*empty*';
      $embed = Logger::initDiscordEmbed($event, $document->getURI(), [
        'Title' => $title,
        'Brief' => $brief,
        'Markdown' => $markdown ? ':white_check_mark:' : ':x:',
      ]);
      $desc = \substr($content, 0, \min(\BNETDocs\Libraries\Discord\Embed::MAX_DESCRIPTION - 13, \strlen($content) - 13));
      if (\strlen($desc) != \strlen($content)) $desc .= '…';
      if (!$markdown) $desc = '```' . \PHP_EOL . $desc . \PHP_EOL . '```';
      $embed->setDescription($desc);
      Logger::logToDiscord($event, $embed);
    }
  }
}
