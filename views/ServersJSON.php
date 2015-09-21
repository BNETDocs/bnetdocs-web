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
    $flags   = (Common::isBrowser(getenv("HTTP_USER_AGENT")) ? JSON_PRETTY_PRINT : 0);
    $content = [];

    foreach ($model->server_types as $server_type) {
      $content["server_types"][] = [
        "id"    => (int)$server_type->getId(),
        "label" =>      $server_type->getLabel()
      ];
    }

    foreach ($model->servers as $server) {
      $created_datetime = $server->getCreatedDateTime();
      $updated_datetime = $server->getUpdatedDateTime();
      $user_id          = $server->getUserId();
      if (!is_null($created_datetime)) $created_datetime = $created_datetime->format("r");
      if (!is_null($updated_datetime)) $updated_datetime = $updated_datetime->format("r");
      if (!is_null($user_id))          $user_id          = (int)$user_id;
      $content["servers"][] = [
        "address"          =>      $server->getAddress(),
        "created_datetime" =>      $created_datetime,
        "id"               => (int)$server->getId(),
        "label"            =>      $server->getLabel(),
        "port"             => (int)$server->getPort(),
        "status_bitmask"   => (int)$server->getStatusBitmask(),
        "type_id"          => (int)$server->getTypeId(),
        "updated_datetime" =>      $updated_datetime,
        "user_id"          =>      $user_id
      ];
    }

    $content["status_bitmasks"] = $model->status_bitmasks;

    echo json_encode($content, $flags);
  }

}
