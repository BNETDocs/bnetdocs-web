<?php

namespace BNETDocs\Controllers\Server;

use \BNETDocs\Libraries\Discord\Embed as DiscordEmbed;
use \BNETDocs\Libraries\Discord\EmbedField as DiscordEmbedField;
use \BNETDocs\Libraries\Discord\Webhook as DiscordWebhook;
use \BNETDocs\Libraries\Exceptions\ServerNotFoundException;
use \BNETDocs\Libraries\Server;

use \BNETDocs\Models\Server\UpdateJob as UpdateJobModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View as ViewLib;

use \DateTime;
use \DateTimeZone;

class UpdateJob extends Controller {

  const S_DISABLED = ':no_entry: Disabled';
  const S_ONLINE   = ':white_check_mark: Online';
  const S_OFFLINE  = ':x: Offline';
  
  public function &run( Router &$router, ViewLib &$view, array &$args ) {
    $model = new UpdateJobModel();
    $action = $router->getRequestMethod();

    if ( $action !== 'POST' ) {

      $model->_responseCode = 405;
      $model->_responseHeaders[ 'Allow' ] = 'POST';

    } else {

      $q = $router->getRequestBodyArray();

      $server_id = ( isset( $q[ 'id' ]) ? $q[ 'id' ] : null );

      $job_token = (
        isset( $q[ 'job_token' ]) ? $q[ 'job_token' ] : null
      );

      $status = (
        isset( $q[ 'status' ]) ? (int) $q[ 'status' ] : null
      );

      if ( !is_null( $server_id )) $server_id = (int) $server_id;
      if ( !is_null( $status )) $status = (int) $status;

      $authenticated = (
        $job_token === Common::$config->bnetdocs->server_update_job_token
      );

      if ( !( is_int( $server_id ) && is_int( $status ))) {
        $model->_responseCode = 400;
      } else if ( !$authenticated ) {
        $model->_responseCode = 403;
      } else {

        try {
          $model->server = new Server( $server_id );
        } catch ( ServerNotFoundException $e ) {
          $model->server = null;
        }

        if ( !$model->server ) {
          $model->_responseCode = 404;
        } else {

          $model->old_status_bitmask = $model->server->getStatusBitmask();
          $model->server->setStatusBitmask( $status );

          if ( $model->server->save() ) {
            $discord = Common::$config->discord->forward_server_updates;
            if ( $discord && $discord->enabled
              && !in_array( $server_id, $discord->ignore_server_ids )) {
              self::dispatchDiscord(
                $model->server, $discord->webhook,
                $model->old_status_bitmask, $status
              );
            }

            $model->_responseCode = 200;
          }
        }
      }
    }

    $view->render( $model );
    return $model;
  }

  protected static function dispatchDiscord( $server, $webhook, $old, $new ) {
    if ($old === $new) return;

    if ($old & Server::STATUS_DISABLED) {
      $old_status = self::S_DISABLED;
    } else if ($old & Server::STATUS_ONLINE) {
      $old_status = self::S_ONLINE;
    } else if (!($old & Server::STATUS_ONLINE)) {
      $old_status = self::S_OFFLINE;
    } else {
      $old_status = sprintf('Unknown (%d)', $old);
    }

    if ($new & Server::STATUS_DISABLED) {
      $title = 'Server Disabled';
      $new_status = self::S_DISABLED;
    } else if ($new & Server::STATUS_ONLINE) {
      $title = 'Server Online';
      $new_status = self::S_ONLINE;
    } else if (!($new & Server::STATUS_ONLINE)) {
      $title = 'Server Offline';
      $new_status = self::S_OFFLINE;
    } else {
      $title = 'Generic Status Change';
      $new_status = sprintf('Unknown (%d)', $new);
    }

    $label = $server->getLabel();

    if (!empty($label)) {
      $title .= ': ' . $label;
    }

    $webhook = new DiscordWebhook($webhook);
    $embed   = new DiscordEmbed();

    $embed->setUrl($server->getURI());
    $embed->setTitle($title);
    $embed->setTimestamp(new DateTime('now', new DateTimeZone('Etc/UTC')));

    $data = array();
    $data['Type'] = $server->getType()->getLabel();

    if (!empty($label)) {
      $data['Label'] = $label;
    }

    $data['Server'] = $server->getAddress() . ':' . $server->getPort();
    $data['Status'] = $old_status . ' → ' . $new_status;

    foreach ($data as $key => $value) {
      $field = new DiscordEmbedField($key, $value, true);
      $embed->addField($field);
    }

    $webhook->addEmbed($embed);
    $webhook->send();
  }
}
