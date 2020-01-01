<?php

namespace BNETDocs\Views\EventLog;

use \BNETDocs\Models\EventLog\View as EventLogViewModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class ViewHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof EventLogViewModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'EventLog/View'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
