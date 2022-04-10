<?php

namespace BNETDocs\Views\Packet;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\Packet\View as PacketViewModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class ViewHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof PacketViewModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Packet/View'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
