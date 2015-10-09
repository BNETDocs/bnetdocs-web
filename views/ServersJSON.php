<?php

namespace BNETDocs\Views;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Server;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Servers as ServersModel;

class ServersJSON extends View {

  public function getMimeType() {
    return "application/json;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof ServersModel) {
      throw new IncorrectModelException();
    }
    $content = [];

    foreach ($model->server_types as $server_type) {
      $content["server_types"][] = [
        "id"    => (int) $server_type->getId(),
        "label" =>       $server_type->getLabel()
      ];
    }

    foreach ($model->servers as $server) {
      $created_datetime = $server->getCreatedDateTime();
      $updated_datetime = $server->getUpdatedDateTime();
      if (!is_null($created_datetime)) $created_datetime = $created_datetime->format("r");
      if (!is_null($updated_datetime)) $updated_datetime = $updated_datetime->format("r");
      $content["servers"][] = [
        "address"          => $server->getAddress(),
        "created_datetime" => $created_datetime,
        "id"               => $server->getId(),
        "label"            => $server->getLabel(),
        "port"             => $server->getPort(),
        "status_bitmask"   => $server->getStatusBitmask(),
        "type_id"          => $server->getTypeId(),
        "updated_datetime" => $updated_datetime,
        "user_id"          => $server->getUserId()
      ];
    }

    $content["status_bitmasks"] = $model->status_bitmasks;

    echo json_encode($content, Common::prettyJSONIfBrowser());
  }

}
