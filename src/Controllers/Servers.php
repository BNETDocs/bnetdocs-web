<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Server as ServerLib;

class Servers extends Base
{
  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Servers();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args): bool
  {
    $q = \BNETDocs\Libraries\Router::query();

    $this->model->server_types    = array();
    $this->model->servers         = ServerLib::getAllServers();
    $this->model->status_bitmasks = [
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

    $status = $q['status'] ?? null;
    $type_id = $q['type_id'] ?? null;
    $user_id = $q['user_id'] ?? null;

    // filter by status bitmask

    if (!is_null($status) && is_numeric($status))
      foreach ($this->model->servers as $it => $server)
        if (!($server->getStatusBitmask() & $status)) unset($this->model->servers[$it]);

    // filter by type_id

    if (!is_null($type_id) && is_numeric($type_id))
      // type_id is a single integer
      foreach ($this->model->servers as $it => $server)
        if ($server->getTypeId() != $type_id) unset($this->model->servers[$it]);

    if (is_string($type_id) && preg_match('/^(?:\d+,?)+$/', $type_id))
      // type_id is a string like "1,2,3"
      $type_id = explode(',', $type_id);

    if ($type_id && is_array($type_id)) {
      foreach ($this->model->servers as $it => $server) {
        $found = false;
        foreach ($type_id as $_type) {
          if ($server->getTypeId() == $_type) {
            $found = true;
            break;
          }
        }
        if (!$found) unset($this->model->servers[$it]);
      }
    }

    // filter by user_id

    if (!is_null($user_id) && is_numeric($user_id))
      // user_id is a single integer
      foreach ($this->model->servers as $it => $server)
        if ($server->getUserId() != $user_id) unset($this->model->servers[$it]);

    if (is_string($user_id) && preg_match('/^(?:\d+,?)+$/', $user_id))
      // user_id is a string like "1,2,3"
      $user_id = explode(',', $user_id);

    if ($user_id && is_array($user_id)) {
      foreach ($this->model->servers as $it => $server) {
        $found = false;
        foreach ($user_id as $_user) {
          if ($server->getUserId() == $_user) {
            $found = true;
            break;
          }
        }
        if (!$found) unset($this->model->servers[$it]);
      }
    }

    // reindex servers after removal of indices using unset()

    $this->model->servers = array_values($this->model->servers);

    // collect types and filter

    $server_types = [];
    foreach ($this->model->servers as $server)
      $server_types[] = $server->getTypeId();
    sort($server_types);
    $server_types = \array_unique($server_types);

    $this->model->server_types = [];
    foreach ($server_types as $id)
      $this->model->server_types[] = new \BNETDocs\Libraries\ServerType($id);

    $this->model->_responseCode = 200;
    return true;
  }
}
