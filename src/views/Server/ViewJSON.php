<?php

namespace BNETDocs\Views\Server;

use \BNETDocs\Models\Server\View as ServerViewModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class ViewJSON extends View {
  public function getMimeType() {
    return 'application/json;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof ServerViewModel) {
      throw new IncorrectModelException();
    }
    echo json_encode(array(
      'server' => $model->server,
      'server_type' => $model->server_type,
    ), Common::prettyJSONIfBrowser());
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
