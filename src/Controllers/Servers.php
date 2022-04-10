<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Server as ServerLib;
use \BNETDocs\Libraries\ServerType as ServerTypeLib;
use \BNETDocs\Models\Servers as ServersModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Servers extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new ServersModel();

    $query = $router->getRequestQueryArray();
    $this->getServers($model, $query);

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }

  protected function getServers(ServersModel &$model, array &$q) {
    $model->server_types    = array();
    $model->servers         = ServerLib::getAllServers();
    $model->status_bitmasks = [
      [
        'bit'         => ServerLib::STATUS_ONLINE,
        'description' => 'Server is online if set, offline if not set',
        'label'       => 'Online'
      ],
      [
        'bit'         => ServerLib::STATUS_DISABLED,
        'description' => 'Server is not automatically checked if set',
        'label'       => 'Disabled'
      ]
    ];

    // collect filter queries

    $status = (isset($q['status']) ? $q['status'] : null);
    $type_id = (isset($q['type_id']) ? $q['type_id'] : null);
    $user_id = (isset($q['user_id']) ? $q['user_id'] : null);

    // filter by status bitmask

    if (!is_null($status) && is_numeric($status)) {
      foreach ($model->servers as $it => $server) {
        if (!($server->getStatusBitmask() & $status)) {
          unset($model->servers[$it]);
        }
      }
    }

    // filter by type_id

    if (!is_null($type_id) && is_numeric($type_id)) {
      // type_id is a single integer
      foreach ($model->servers as $it => $server) {
        if ($server->getTypeId() != $type_id) {
          unset($model->servers[$it]);
        }
      }
    }

    if (is_string($type_id) && preg_match('/^(?:\d+,?)+$/', $type_id)) {
      // type_id is a string like "1,2,3"
      $type_id = explode(',', $type_id);
    }

    if ($type_id && is_array($type_id)) {
      foreach ($model->servers as $it => $server) {
        $found = false;
        foreach ($type_id as $_type) {
          if ($server->getTypeId() == $_type) {
            $found = true;
            break;
          }
        }
        if (!$found) unset($model->servers[$it]);
      }
    }

    // filter by user_id

    if (!is_null($user_id) && is_numeric($user_id)) {
      // user_id is a single integer
      foreach ($model->servers as $it => $server) {
        if ($server->getUserId() != $user_id) {
          unset($model->servers[$it]);
        }
      }
    }

    if (is_string($user_id) && preg_match('/^(?:\d+,?)+$/', $user_id)) {
      // user_id is a string like "1,2,3"
      $user_id = explode(',', $user_id);
    }

    if ($user_id && is_array($user_id)) {
      foreach ($model->servers as $it => $server) {
        $found = false;
        foreach ($user_id as $_user) {
          if ($server->getUserId() == $_user) {
            $found = true;
            break;
          }
        }
        if (!$found) unset($model->servers[$it]);
      }
    }

    // reindex servers after removal of indices using unset()

    $model->servers = array_values($model->servers);

    // collect types and filter

    $server_types = array();
    foreach ($model->servers as $server) {
      $server_types[] = $server->getTypeId();
    }
    sort($server_types);
    $server_types = array_unique($server_types);

    $model->server_types = array();
    foreach ($server_types as $id) {
      $model->server_types[] = new ServerTypeLib($id);
    }

  }
}
