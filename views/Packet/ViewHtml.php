<?php

namespace BNETDocs\Views\Packet;

use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Packet\View as PacketViewModel;

class ViewHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof PacketViewModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "Packet/View"))->render();
  }

}
