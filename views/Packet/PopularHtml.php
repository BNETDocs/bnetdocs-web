<?php

namespace BNETDocs\Views\Packet;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Packet\Popular as PacketPopularModel;

class PopularHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof PacketPopularModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "Packet/Popular"))->render();
  }

}
