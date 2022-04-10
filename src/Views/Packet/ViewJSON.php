<?php

namespace BNETDocs\Views\Packet;

use \BNETDocs\Models\Packet\View as PacketViewModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class ViewJSON extends View {
  public function getMimeType() {
    return 'application/json;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof PacketViewModel) {
      throw new IncorrectModelException();
    }
    echo json_encode($model->packet, Common::prettyJSONIfBrowser());
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
