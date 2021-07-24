<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\Document\Create as DocumentCreateModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Create extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model               = new DocumentCreateModel();
    $model->error        = null;
    $model->user         = Authentication::$user;

    $model->acl_allowed = ($model->user && $model->user->getOption(
      User::OPTION_ACL_DOCUMENT_CREATE
    ));

    if ($router->getRequestMethod() == 'POST') {
      $this->handlePost($router, $model);
    } else if ($router->getRequestMethod() == 'GET') {
      $model->markdown = true;
    }

    $view->render($model);
    $model->_responseCode = ($model->acl_allowed ? 200 : 403);
    return $model;
  }

  protected function handlePost(Router &$router, DocumentCreateModel &$model) {
    if (!$model->acl_allowed) {
      $model->error = 'ACL_NOT_SET';
      return;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $data       = $router->getRequestBodyArray();
    $title      = (isset($data['title'     ]) ? $data['title'     ] : null);
    $markdown   = (isset($data['markdown'  ]) ? $data['markdown'  ] : null);
    $content    = (isset($data['content'   ]) ? $data['content'   ] : null);
    $publish    = (isset($data['publish'   ]) ? $data['publish'   ] : null);
    $save       = (isset($data['save'      ]) ? $data['save'      ] : null);

    $model->title    = $title;
    $model->markdown = $markdown;
    $model->content  = $content;

    if (empty($title)) {
      $model->error = 'EMPTY_TITLE';
    } else if (empty($content)) {
      $model->error = 'EMPTY_CONTENT';
    }

    $options_bitmask = 0;
    if ($markdown) $options_bitmask |= Document::OPTION_MARKDOWN;
    if ($publish ) $options_bitmask |= Document::OPTION_PUBLISHED;

    $user_id = $model->user->getId();

    try {

      $document = new Document(null);
      $document->setContent($content);
      $document->setOptions($options_bitmask);
      $document->setTitle($title);
      $document->setUserId($user_id);
      $document->commit();
      $model->error = false;

    } catch (QueryException $e) {

      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);
      $model->error = 'INTERNAL_ERROR';

    }

    Logger::logEvent(
      EventTypes::DOCUMENT_CREATED,
      $user_id,
      getenv('REMOTE_ADDR'),
      json_encode([
        'error'           => $model->error,
        'options_bitmask' => $options_bitmask,
        'title'           => $title,
        'content'         => $content,
      ])
    );
  }
}
