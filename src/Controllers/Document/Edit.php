<?php
namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\DocumentNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\Document\Edit as DocumentEditModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \UnexpectedValueException;

class Edit extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $data = $router->getRequestQueryArray();
    $model = new DocumentEditModel();
    $model->document_id = (isset($data['id']) ? $data['id'] : null);
    $model->user = Authentication::$user;

    $model->acl_allowed = ($model->user && $model->user->getOption(
      User::OPTION_ACL_DOCUMENT_MODIFY
    ));

    try { $model->document = new Document($model->document_id); }
    catch (DocumentNotFoundException $e) { $model->document = null; }
    catch (InvalidArgumentException $e) { $model->document = null; }
    catch (UnexpectedValueException $e) { $model->document = null; }

    if ($model->document === null) {
      $model->error = 'NOT_FOUND';
    } else {
      $model->comments = Comment::getAll(
        Comment::PARENT_TYPE_DOCUMENT,
        $model->document_id
      );

      $model->brief     = $model->document->getBrief(false);
      $model->content   = $model->document->getContent(false);
      $model->markdown  = $model->document->isMarkdown();
      $model->published = $model->document->isPublished();
      $model->title     = $model->document->getTitle();

      if ($router->getRequestMethod() == 'POST') {
        $this->handlePost($router, $model);
      }
    }

    $view->render($model);
    $model->_responseCode = ($model->acl_allowed ? 200 : 401);
    return $model;
  }

  protected function handlePost(Router &$router, DocumentEditModel &$model) {
    if (!$model->acl_allowed) {
      $model->error = 'ACL_NOT_SET';
      return;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $data = $router->getRequestBodyArray();
    $brief = $data['brief'] ?? null;
    $category = $data['category'] ?? null;
    $content = $data['content'] ?? null;
    $markdown = $data['markdown'] ?? null;
    $publish = $data['publish'] ?? null;
    $save = $data['save'] ?? null;
    $title = $data['title'] ?? null;

    $markdown = ($markdown ? true : false);
    $publish = ($publish ? true : false);

    $model->category = $category;
    $model->title    = $title;
    $model->brief    = $brief;
    $model->markdown = $markdown;
    $model->content  = $content;

    if (empty($title)) {
      $model->error = 'EMPTY_TITLE';
    } else if (empty($content)) {
      $model->error = 'EMPTY_CONTENT';
    }

    $user_id = $model->user->getId();

    try {

      $model->document->setTitle($model->title);
      $model->document->setBrief($model->brief);
      $model->document->setMarkdown($markdown);
      $model->document->setContent($model->content);
      $model->document->setPublished($publish);

      $model->document->incrementEdited();
      $model->document->commit();
      $model->error = false;

    } catch (QueryException $e) {

      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);
      $model->error = 'INTERNAL_ERROR';

    }

    Logger::logEvent(
      EventTypes::DOCUMENT_EDITED,
      $user_id,
      getenv('REMOTE_ADDR'),
      json_encode([
        'brief'           => $model->document->getBrief(false),
        'content'         => $model->document->getContent(false),
        'document_id'     => $model->document_id,
        'error'           => $model->error,
        'options_bitmask' => $model->document->getOptions(),
        'title'           => $model->document->getTitle(),
      ])
    );
  }
}
