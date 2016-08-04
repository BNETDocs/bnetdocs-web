<?php

namespace BNETDocs\Views\Packet;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Packet\Index as PacketIndexModel;

class IndexHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof PacketIndexModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "Packet/Index"))->render();
  }

}
