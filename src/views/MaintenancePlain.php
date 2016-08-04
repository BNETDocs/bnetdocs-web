<?php

namespace BNETDocs\Views;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Maintenance as MaintenanceModel;

class MaintenancePlain extends View {

  public function getMimeType() {
    return "text/plain;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof MaintenanceModel) {
      throw new IncorrectModelException();
    }
    echo "Maintenance - BNETDocs\n" . $model->message . "\n";
  }

}
