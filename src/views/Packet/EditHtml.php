<?php

namespace BNETDocs\Views\Packet;

use \BNETDocs\Models\Packet\Edit as PacketEditModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class EditHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof PacketEditModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "Packet/Edit"))->render();
  }

}
