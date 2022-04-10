<?php

namespace BNETDocs\Views;

use \BNETDocs\Libraries\Server;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\Servers as ServersModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class ServersJSON extends View {

  public function getMimeType() {
    return 'application/json;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof ServersModel) {
      throw new IncorrectModelException();
    }
    $content = [];

    foreach ($model->server_types as $server_type) {
      $content['server_types'][] = [
        'id'    => (int) $server_type->getId(),
        'label' =>       $server_type->getLabel()
      ];
    }

    foreach ($model->servers as $server) {
      $content['servers'][] = $server;
    }

    $content['status_bitmasks'] = $model->status_bitmasks;

    echo json_encode($content, Common::prettyJSONIfBrowser());
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }

}
