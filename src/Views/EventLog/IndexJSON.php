<?php

namespace BNETDocs\Views\EventLog;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;
use \BNETDocs\Models\EventLog\Index as EventLogIndexModel;

class IndexJSON extends View {
  public function getMimeType() {
    return 'application/json;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof EventLogIndexModel) {
      throw new IncorrectModelException();
    }
    echo json_encode([
      'event_log' => $model->event_log
    ], Common::prettyJSONIfBrowser());
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
