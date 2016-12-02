<?php

namespace BNETDocs\Controllers\Attachment;

use \BNETDocs\Libraries\Attachment;
use \BNETDocs\Libraries\Exceptions\AttachmentNotFoundException;
use \BNETDocs\Models\Attachment\Download as DownloadModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Database;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Download extends Controller {

  const TTL_ONE_YEAR = 31536000;

  public function &run(Router &$router, View &$view, array &$args) {

    $data = $router->getRequestQueryArray();

    $model = new DownloadModel();
    $model->attachment_id = (isset($data['id']) ? (int) $data['id'] : null);

    try {
      $model->attachment = new Attachment($id);
    } catch (AttachmentNotFoundException $e) {
      $model->attachment = null;
    }

    $view->render($model);

    $model->_responseCode = ($model->attachment ? 200 : 404);
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = self::TTL_ONE_YEAR;

    if ($model->extra_headers) {
      foreach ($model->extra_headers as $name => $value) {
        $model->_responseHeaders[$name] = $value;
      }
    }

    return $model;

  }

}
