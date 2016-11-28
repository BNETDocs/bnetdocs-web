<?php

namespace BNETDocs\Views\Packet;

use \BNETDocs\Models\Packet\Index as PacketIndexModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class IndexJSON extends View {

  public function getMimeType() {
    return "application/json;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof PacketIndexModel) {
      throw new IncorrectModelException();
    }
    echo json_encode([
      "packets" => $model->packets
    ], Common::prettyJSONIfBrowser());
  }

}
