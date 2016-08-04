<?php

namespace BNETDocs\Views;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Maintenance as MaintenanceModel;

class MaintenanceJSON extends View {

  public function getMimeType() {
    return "application/json;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof MaintenanceModel) {
      throw new IncorrectModelException();
    }
    echo json_encode([
      "title"   => "Maintenance - BNETDocs",
      "message" => $model->message
    ], Common::prettyJSONIfBrowser());
  }

}
