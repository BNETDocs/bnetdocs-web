<?php

namespace BNETDocs\Views\Server;

use \BNETDocs\Libraries\ArrayFlattener;
use \BNETDocs\Libraries\Server;
use \BNETDocs\Models\Server\View as ServerViewModel;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class ViewPlain extends View {

  public function getMimeType() {
    return 'text/plain;charset=utf-8';
  }

  public function render( Model &$model ) {
    if ( !$model instanceof ServerViewModel ) {
      throw new IncorrectModelException();
    }

    if ( !$model->server ) {
      echo '';
      return;
    }

    $status_i = $model->server->getStatusBitmask();
    $status_s = (
      ( $status_i & Server::STATUS_DISABLED ) ? 'disabled' : (
        ( $status_i & Server::STATUS_ONLINE ) ? 'online' : 'offline'
      )
    );

    $updated = $model->server->getUpdatedDateTime();
    $updated = ( $updated ? ' '
      . $updated->format( 'U' ) . ' ' . $updated->format( DATE_RFC2822 ) : ''
    );

    $user = $model->server->getUser();
    $user = ( $user ? ' ' . $user->getId() . ' ' . $user->getName() : '' );

    echo 'address ' . $model->server->getAddress() . PHP_EOL;
    echo 'created_datetime '
      . $model->server->getCreatedDateTime()->format( 'U' ) . ' '
      . $model->server->getCreatedDateTime()->format( DATE_RFC2822 ) . PHP_EOL;
    echo 'id ' . $model->server_id . PHP_EOL;
    echo 'label ' . $model->server->getLabel() . PHP_EOL;
    echo 'port ' . $model->server->getPort() . PHP_EOL;
    echo 'status ' . $status_s . PHP_EOL;
    echo 'type ' . $model->server_type->getId() . ' '
      . $model->server_type->getLabel() . PHP_EOL;
    echo 'updated_datetime' . $updated . PHP_EOL;
    echo 'user' . $user . PHP_EOL;
  }

}
