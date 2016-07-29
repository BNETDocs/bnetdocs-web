<?php

namespace BNETDocs\Views\EventLog;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\EventLog\Index as EventLogIndexModel;

class IndexHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof EventLogIndexModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "EventLog/Index"))->render();
  }

}
