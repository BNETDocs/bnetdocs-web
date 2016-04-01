<?php

namespace BNETDocs\Views\Packet;

use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Packet\View as PacketViewModel;

class ViewPlain extends View {

  public function getMimeType() {
    return "text/plain;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof PacketViewModel) {
      throw new IncorrectModelException();
    }
    echo $model->packet->getPacketName() . "\n";
    echo str_repeat("=", strlen($model->packet->getPacketName())) . "\n\n";
    echo $model->packet->getPacketRemarks(false);
  }

}
