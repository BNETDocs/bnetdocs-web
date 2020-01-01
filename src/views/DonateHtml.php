<?php

namespace BNETDocs\Views;

use \BNETDocs\Models\Donate as DonateModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class DonateHtml extends View {

  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof DonateModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Donate'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }

}
