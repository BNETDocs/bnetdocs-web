<?php

namespace BNETDocs\Views\Packet;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\Packet\Index as PacketIndexModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class IndexHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof PacketIndexModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Packet/Index'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
