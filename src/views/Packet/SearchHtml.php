<?php

namespace BNETDocs\Views\Packet;

use \BNETDocs\Models\Packet\Search as PacketSearchModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class SearchHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof PacketSearchModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Packet/Search'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
