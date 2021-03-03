<?php

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\Packet;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\PacketNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\Packet\Delete as PacketDeleteModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \InvalidArgumentException;

class Delete extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $data                = $router->getRequestQueryArray();
    $model               = new PacketDeleteModel();
    $model->error        = null;
    $model->id           = (isset($data['id']) ? $data['id'] : null);
    $model->packet       = null;
    $model->title        = null;
    $model->user         = Authentication::$user;

    $model->acl_allowed = ($model->user && $model->user->getAcl(
      User::OPTION_ACL_PACKET_DELETE
    ));

    try { $model->packet = new Packet($model->id); }
    catch (PacketNotFoundException $e) { $model->packet = null; }
    catch (InvalidArgumentException $e) { $model->packet = null; }

    if ($model->packet === null) {
      $model->error = 'NOT_FOUND';
    } else {
      $model->title = $model->packet->getPacketDirectionTag() .
        ' ' . $model->packet->getPacketName();

      if ($router->getRequestMethod() == 'POST') {
        $this->tryDelete($router, $model);
      }
    }

    $view->render($model);
    $model->_responseCode = ($model->acl_allowed ? 200 : 403);
    return $model;
  }

  protected function tryDelete(Router &$router, PacketDeleteModel &$model) {
    if (!isset($model->user)) {
      $model->error = 'NOT_LOGGED_IN';
      return;
    }

    if (!$model->acl_allowed) {
      $model->error = 'ACL_NOT_SET';
      return;
    }

    $model->error = false;

    $id      = (int) $model->id;
    $user_id = $model->user->getId();

    try {

      $success = Packet::delete($id);

    } catch (QueryException $e) {

      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);

      $success = false;

    }

    if (!$success) {
      $model->error = 'INTERNAL_ERROR';
    } else {
      $model->error = false;
    }

    Logger::logEvent(
      EventTypes::PACKET_DELETED,
      $user_id,
      getenv('REMOTE_ADDR'),
      json_encode([
        'error'     => $model->error,
        'packet_id' => $id,
      ])
    );
  }
}
