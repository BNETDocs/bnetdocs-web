<?php

namespace BNETDocs\Views\EventLog;

use \BNETDocs\Models\EventLog\Index as EventLogIndexModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

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
