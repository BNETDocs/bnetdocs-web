<?php

namespace BNETDocs\Views;

use \BNETDocs\Models\Status as StatusModel;
use \BNETDocs\Libraries\ArrayFlattener;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;
use \RecursiveArrayIterator;
use \RecursiveIteratorIterator;

class StatusPlain extends View {

  public function getMimeType() {
    return 'text/plain;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof StatusModel) {
      throw new IncorrectModelException();
    }
    echo ArrayFlattener::flatten( $model );
  }

}
