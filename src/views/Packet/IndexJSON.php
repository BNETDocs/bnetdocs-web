<?php

namespace BNETDocs\Views\Packet;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Packet\Index as PacketIndexModel;

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
