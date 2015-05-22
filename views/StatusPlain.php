<?php

namespace BNETDocs\Views;

use BNETDocs\Libraries\Common;
use BNETDocs\Libraries\Exceptions\IncorrectModelException;
use BNETDocs\Libraries\Model;
use BNETDocs\Libraries\View;
use BNETDocs\Models\Status as StatusModel;

class StatusPlain extends View {

  public function getMimeType() {
    return "text/plain;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof StatusModel) {
      throw new IncorrectModelException();
    }
    echo "newrelic_version " . (extension_loaded("newrelic") ? (new \ReflectionExtension("newrelic"))->getVersion() : "null") . "\n";
    echo "remote_address " . $model->remote_address . "\n";
    if ($model->remote_geoinfo) {
      foreach ($model->remote_geoinfo as $key => $val) {
        if (!empty($val))
          echo "remote_geoinfo_" . $key . " " . $val . "\n";
      }
    } else if (is_bool($model->remote_geoinfo)) {
      echo "remote_geoinfo " . ($model->remote_geoinfo ? "true" : "false") . "\n";
    } else if (is_null($model->remote_geoinfo)) {
      echo "remote_geoinfo null\n";
    } else {
      echo "remote_geoinfo " . gettype($model->remote_geoinfo) . "\n";
    }
    echo "timestamp " . $model->timestamp->format($model->timestamp_format) . "\n";
  }

}
