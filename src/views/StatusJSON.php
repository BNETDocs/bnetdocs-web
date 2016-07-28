<?php

namespace BNETDocs\Views;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Status as StatusModel;

class StatusJSON extends View {

  public function getMimeType() {
    return "application/json;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof StatusModel) {
      throw new IncorrectModelException();
    }
    echo json_encode([
      "healthcheck"    => $model->healthcheck,
      "remote_address" => $model->remote_address,
      "remote_geoinfo" => $model->remote_geoinfo,
      "timestamp"      => $model->timestamp->format("r"),
      "version_info"   => $model->version_info,
    ], Common::prettyJSONIfBrowser());
  }

}
