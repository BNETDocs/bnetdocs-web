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

class Edit extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $data                = $router->getRequestQueryArray();
    $model               = new DocumentEditModel();
    $model->content      = null;
    $model->document     = null;
    $model->document_id  = (isset($data['id']) ? $data['id'] : null);
    $model->error        = null;
    $model->markdown     = null;
    $model->published    = null;
    $model->title        = null;
    $model->user         = Authentication::$user;

    $model->acl_allowed = ($model->user && $model->user->getOption(
      User::OPTION_ACL_DOCUMENT_MODIFY
    ));

    try { $model->document = new Document($model->document_id); }
    catch (DocumentNotFoundException $e) { $model->document = null; }
    catch (InvalidArgumentException $e) { $model->document = null; }

    if ($model->document === null) {
      $model->error = 'NOT_FOUND';
    } else {
      $model->comments = Comment::getAll(
        Comment::PARENT_TYPE_DOCUMENT,
        $model->document_id
      );

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

    $data       = $router->getRequestBodyArray();
    $category   = (isset($data['category'  ]) ? $data['category'  ] : null);
    $title      = (isset($data['title'     ]) ? $data['title'     ] : null);
    $markdown   = (isset($data['markdown'  ]) ? $data['markdown'  ] : null);
    $content    = (isset($data['content'   ]) ? $data['content'   ] : null);
    $publish    = (isset($data['publish'   ]) ? $data['publish'   ] : null);
    $save       = (isset($data['save'      ]) ? $data['save'      ] : null);

    $model->category = $category;
    $model->title    = $title;
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
      $model->document->setMarkdown($model->markdown);
      $model->document->setContent($model->content);
      $model->document->setPublished($publish ? true : false);

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
        'error'           => $model->error,
        'document_id'     => $model->document_id,
        'options_bitmask' => $model->document->getOptions(),
        'title'           => $model->document->getTitle(),
        'content'         => $model->document->getContent(false),
      ])
    );
  }
}
