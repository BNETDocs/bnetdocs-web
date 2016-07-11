<?php

namespace BNETDocs\Controllers\Attachment;

use \BNETDocs\Libraries\Attachment;
use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\AttachmentNotFoundException;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Attachment\Download as DownloadModel;
use \BNETDocs\Views\Attachment\DownloadRaw as DownloadRawView;

class Download extends Controller {

  const TTL_ONE_YEAR = 31536000;

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "":
        $view = new DownloadRawView();
      break;
      default:
        throw new UnspecifiedViewException();
    }

    $data = $router->getRequestQueryArray();
    $id   = (isset($data["id"]) ? (int) $data["id"] : null);

    $model                = new DownloadModel();
    $model->attachment_id = $id;

    try {
      $model->attachment = new Attachment($id);
    } catch (AttachmentNotFoundException $e) {
      $model->attachment = null;
    }

    ob_start();
    $view->render($model);
    $router->setResponseCode(($model->attachment ? 200 : 404));
    $router->setResponseTTL(self::TTL_ONE_YEAR);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    if ($model->extra_headers) {
      foreach ($model->extra_headers as $name => $value) {
        $router->setResponseHeader($name, $value);
      }
    }
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
