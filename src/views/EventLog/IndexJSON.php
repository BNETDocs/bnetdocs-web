<?php

namespace BNETDocs\Views\EventLog;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\EventLog\Index as EventLogIndexModel;

class IndexJSON extends View {

  public function getMimeType() {
    return "application/json;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof EventLogIndexModel) {
      throw new IncorrectModelException();
    }
    echo json_encode([
      "event_log" => $model->event_log
    ], Common::prettyJSONIfBrowser());
  }

}
