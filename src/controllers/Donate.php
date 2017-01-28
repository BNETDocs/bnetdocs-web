<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\VersionInfo;
use \BNETDocs\Models\Donate as DonateModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \DateTime;
use \DateTimeZone;

class Donate extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model              = new DonateModel();
    $model->btc_address = Common::$config->bnetdocs->btc_donate_address;

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
