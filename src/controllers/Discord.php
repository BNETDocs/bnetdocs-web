<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Models\Discord as DiscordModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \DateTime;
use \DateTimeZone;

class Discord extends Controller {

  public function &run( Router &$router, View &$view, array &$args ) {

    $model = new DiscordModel();

    $model->discord_url = 'https://discord.gg/';
    $model->discord_url .= Common::$config->discord->invite_code;
    $model->discord_server_id = Common::$config->discord->server_id;

    $view->render( $model );

    $model->_responseCode = 200;
    $model->_responseHeaders[ 'Content-Type' ] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
