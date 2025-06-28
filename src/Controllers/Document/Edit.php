<?php
namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Core\HttpCode;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Models\Document\Edit as EditModel;

class Edit extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new EditModel();
  }

  public function invoke(?array $args): bool
  {
    $this->model->acl_allowed = $this->model->active_user
      && $this->model->active_user->getOption(\BNETDocs\Libraries\User\User::OPTION_ACL_DOCUMENT_MODIFY);

    $this->model->document_id = Router::query()['id'] ?? null;

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = HttpCode::HTTP_FORBIDDEN;
      $this->model->error = $this->model->active_user ? EditModel::ERROR_ACL_NOT_SET : EditModel::ERROR_NOT_LOGGED_IN;
      return true;
    }

    try { $this->model->document = new \BNETDocs\Libraries\Document($this->model->document_id); }
    catch (\UnexpectedValueException) { $this->model->document = null; }

    if (!$this->model->document)
    {
      $this->model->_responseCode = HttpCode::HTTP_NOT_FOUND;
      $this->model->error = EditModel::ERROR_NOT_FOUND;
      return true;
    }

    $this->model->comments = Comment::getAll(Comment::PARENT_TYPE_DOCUMENT, $this->model->document_id);
    $this->model->brief = $this->model->document->getBrief(false);
    $this->model->content = $this->model->document->getContent(false);
    $this->model->markdown = $this->model->document->isMarkdown();
    $this->model->published = $this->model->document->isPublished();
    $this->model->title = $this->model->document->getTitle();

    if (Router::requestMethod() == Router::METHOD_POST) $this->handlePost();

    $this->model->_responseCode = HttpCode::HTTP_OK;
    return true;
  }

  protected function handlePost(): void
  {
    $q = Router::query();
    $brief = $q['brief'] ?? null;
    $category = $q['category'] ?? null;
    $content = $q['content'] ?? null;
    $markdown = $q['markdown'] ?? null;
    $publish = $q['publish'] ?? null;
    $title = $q['title'] ?? null;

    $markdown = ($markdown ? true : false);
    $publish = ($publish ? true : false);

    $this->model->category = $category;
    $this->model->title = $title;
    $this->model->brief = $brief;
    $this->model->markdown = $markdown;
    $this->model->content = $content;

    if (empty($title))
    {
      $this->model->error = EditModel::ERROR_EMPTY_TITLE;
    }
    else if (empty($content))
    {
      $this->model->error = EditModel::ERROR_EMPTY_CONTENT;
    }

    if ($this->model->error) return;

    $this->model->document->setTitle($this->model->title);
    $this->model->document->setBrief($this->model->brief);
    $this->model->document->setMarkdown($markdown);
    $this->model->document->setContent($this->model->content);
    $this->model->document->setPublished($publish);
    $this->model->document->incrementEdited();

    $this->model->error = $this->model->document->commit() ? false : EditModel::ERROR_INTERNAL;
    if ($this->model->error !== false) return;

    $event = Logger::initEvent(
      \BNETDocs\Libraries\EventLog\EventTypes::DOCUMENT_EDITED,
      $this->model->active_user,
      getenv('REMOTE_ADDR'),
      [
        'brief'           => $this->model->document->getBrief(false),
        'content'         => $this->model->document->getContent(false),
        'document_id'     => $this->model->document_id,
        'error'           => $this->model->error,
        'options_bitmask' => $this->model->document->getOptions(),
        'title'           => $this->model->document->getTitle(),
      ]
    );

    if ($event->commit())
    {
      $brief = $this->model->document->getBrief(false);
      if (empty($brief)) $brief = '*empty*';

      $content = $this->model->document->getContent(false);
      $markdown = $this->model->document->isMarkdown();
      $user = $this->model->document->getUser();

      $embed = Logger::initDiscordEmbed($event, $this->model->document->getURI(), [
        'Title' => $this->model->document->getTitle(),
        'Brief' => $brief,
        'Markdown' => $markdown ? ':white_check_mark:' : ':x:',
        'Authored by' => !\is_null($user) ? $user->getAsMarkdown() : '*Anonymous*',
        'Edited by' => $this->model->active_user->getAsMarkdown(),
      ]);

      $desc = \substr($content, 0, \min(\BNETDocs\Libraries\Discord\Embed::MAX_DESCRIPTION - 13, \strlen($content) - 13));
      if (\strlen($desc) != \strlen($content)) $desc .= '…';
      if (!$markdown) $desc = '```' . \PHP_EOL . $desc . \PHP_EOL . '```';
      $embed->setDescription($desc);

      Logger::logToDiscord($event, $embed);
    }
  }
}
