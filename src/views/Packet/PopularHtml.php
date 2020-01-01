<?php

namespace BNETDocs\Views\Packet;

use \BNETDocs\Models\Packet\Popular as PacketPopularModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class PopularHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof PacketPopularModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Packet/Popular'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
