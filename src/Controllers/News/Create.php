<?php

namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\NewsCategory;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\News\Create as NewsCreateModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Create extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model                  = new NewsCreateModel();
    $model->error           = null;
    $model->news_categories = null;
    $model->user            = Authentication::$user;

    $model->acl_allowed = ($model->user && $model->user->getOption(
      User::OPTION_ACL_NEWS_CREATE
    ));

    $model->news_categories = NewsCategory::getAll();
    usort($model->news_categories, function($a, $b){
      $oA = $a->getSortId();
      $oB = $b->getSortId();
      if ($oA == $oB) return 0;
      return ($oA < $oB) ? -1 : 1;
    });

    if ($router->getRequestMethod() == 'POST') {
      $this->handlePost($router, $model);
    } else if ($router->getRequestMethod() == 'GET') {
      $model->markdown   = true;
      $model->rss_exempt = false;
    }

    $view->render($model);
    $model->_responseCode = ($model->acl_allowed ? 200 : 403);
    return $model;
  }

  protected function handlePost(Router &$router, NewsCreateModel &$model) {
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
    $rss_exempt = (isset($data['rss_exempt']) ? $data['rss_exempt'] : null);
    $publish    = (isset($data['publish'   ]) ? $data['publish'   ] : null);
    $save       = (isset($data['save'      ]) ? $data['save'      ] : null);

    $model->category   = $category;
    $model->title      = $title;
    $model->markdown   = $markdown;
    $model->content    = $content;
    $model->rss_exempt = $rss_exempt;

    if (empty($title)) {
      $model->error = 'EMPTY_TITLE';
    } else if (empty($content)) {
      $model->error = 'EMPTY_CONTENT';
    }

    $options_bitmask = 0;
    if ($markdown  ) $options_bitmask |= NewsPost::OPTION_MARKDOWN;
    if ($rss_exempt) $options_bitmask |= NewsPost::OPTION_RSS_EXEMPT;
    if ($publish   ) $options_bitmask |= NewsPost::OPTION_PUBLISHED;

    try {

      $success = NewsPost::create(
        $model->user->getId(), $category, $options_bitmask, $title, $content
      );

    } catch (QueryException $e) {

      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);

    }

    if (!$success) {
      $model->error = 'INTERNAL_ERROR';
    } else {
      $model->error = false;
    }

    Logger::logEvent(
      EventTypes::NEWS_CREATED,
      $model->user->getId(),
      getenv('REMOTE_ADDR'),
      json_encode([
        'error'           => $model->error,
        'category_id'     => $category,
        'options_bitmask' => $options_bitmask,
        'title'           => $title,
        'content'         => $content,
      ])
    );
  }
}
