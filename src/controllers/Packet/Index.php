<?php

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Packet;
use \BNETDocs\Models\Packet\Index as PacketIndexModel;
use \BNETDocs\Views\Packet\IndexHtml as PacketIndexHtmlView;
use \BNETDocs\Views\Packet\IndexJSON as PacketIndexJSONView;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Gravatar;
use \CarlBennett\MVC\Libraries\Pair;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \DateTime;
use \DateTimeZone;

class Index extends Controller {

  public function &run( Router &$router, View &$view, array &$args ) {

    $model = new PacketIndexModel();

    $query = $router->getRequestQueryArray();

    $model->order = (
      isset( $query['order'] ) ? $query['order'] : 'packet-id-asc'
    );

    switch ( $model->order ) {
      case 'created-datetime-asc':
        $order = [ 'created_datetime','ASC' ]; break;

      case 'created-datetime-desc':
        $order = [ 'created_datetime','DESC' ]; break;

      case 'id-asc':
        $order = [ 'id','ASC' ]; break;

      case 'id-desc':
        $order = [ 'id','DESC' ]; break;

      case 'packet-id-asc':
        $order = [ 'packet_application_layer_id,packet_id','ASC' ]; break;

      case 'packet-id-desc':
        $order = [ 'packet_application_layer_id,packet_id','DESC' ]; break;

      case 'user-id-asc':
        $order = [ 'user_id','ASC' ]; break;

      case 'user-id-desc':
        $order = [ 'user_id','DESC' ]; break;

      default:
        $order = null;
    }

    $model->packets = Packet::getAllPackets( $order );

    if ( !( $view instanceof PacketIndexHtmlView
      || $view instanceof PacketIndexJSONView )) {

      $model->packets = self::disambiguify( $model->packets );

    }

    if ( !$view instanceof PacketIndexHtmlView ) {
      $model->timestamp = new DateTime( 'now', new DateTimeZone( 'Etc/UTC' ));
    }

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

  private static function &disambiguify( &$packets ) {
    $pkts = [];

    foreach ( $packets as $pkt ) {
      // This removes duplicates by overwriting keys that already exist
      $pkts[ $pkt->getPacketId() . $pkt->getPacketName() ] = $pkt;
    }

    return $pkts;
  }

}
