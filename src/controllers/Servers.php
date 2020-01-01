<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Server as ServerLib;
use \BNETDocs\Libraries\ServerType as ServerTypeLib;
use \BNETDocs\Models\Servers as ServersModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Servers extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model = new ServersModel();

    $this->getServers($model);

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();

    return $model;

  }

  protected function getServers(ServersModel &$model) {
    $model->server_types    = ServerTypeLib::getAllServerTypes();
    $model->servers         = ServerLib::getAllServers();
    $model->status_bitmasks = [
      [
        "bit"         => ServerLib::STATUS_ONLINE,
        "description" => "Server is online if set, offline if not set",
        "label"       => "Online"
      ],
      [
        "bit"         => ServerLib::STATUS_DISABLED,
        "description" => "Server is not automatically checked if set",
        "label"       => "Disabled"
      ]
    ];
  }

}
