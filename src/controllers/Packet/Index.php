<?php

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Packet;
use \BNETDocs\Libraries\UserSession;
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

  public function &run(Router &$router, View &$view, array &$args) {

    $model               = new PacketIndexModel();
    $model->packets      = Packet::getAllPackets();
    $model->user_session = UserSession::load($router);

    // Alphabetically sort the packets for non-json
    if (!$view instanceof PacketIndexJSONView && $model->packets) {
      usort($model->packets, function($a, $b){
        $a1 = $a->getPacketApplicationLayerId();
        $b1 = $b->getPacketApplicationLayerId();
        if ($a1 == $b1) {
          $a2 = $a->getPacketId();
          $b2 = $b->getPacketId();
          if ($a2 == $b2) {
            $a3 = $a->getPacketDirectionId();
            $b3 = $b->getPacketDirectionId();
            if ($a3 == $b3) return 0;
            return ($a3 < $a3 ? -1 : 1);
          }
          return ($a2 < $b2 ? -1 : 1);
        }
        return ($a1 < $b1 ? -1 : 1);
      });
    }

    // Remove packets that are not published
    if ($model->packets) {
      $i = count($model->packets) - 1;
      while ($i >= 0) {
        if (!($model->packets[$i]->getOptionsBitmask()
          & Packet::OPTION_PUBLISHED)) {
          unset($model->packets[$i]);
        }
        --$i;
      }
    }

    // Include timestamp if non-html
    if (!$view instanceof PacketIndexHtmlView) {
      $model->timestamp = new DateTime("now", new DateTimeZone("UTC"));
    }

    // Objectify for JSON
    if ($view instanceof PacketIndexJSONView) {
      $packets = [];
      foreach ($model->packets as $packet) {
        $user = $packet->getUser();
        if ($user) {
          $user = [
            "avatar_url" => "https:"
              . (new Gravatar($user->getEmail()))->getUrl(null, "identicon"),
            "id"     => $user->getId(),
            "name"   => $user->getName(),
            "url"    => Common::relativeUrlToAbsolute(
              "/user/" . $user->getId() . "/"
              . Common::sanitizeForUrl($user->getName())
            )
          ];
        }
        $packets[] = [
          "created_datetime" => self::renderDateTime(
                                  $packet->getCreatedDateTime()
                                ),
          "edited_count"     => $packet->getEditedCount(),
          "edited_datetime"  => self::renderDateTime(
                                  $packet->getEditedDateTime()
                                ),
          "id"               => $packet->getId(),
          "options_bitmask"  => $packet->getOptionsBitmask(),
          "packet_transport_layer_id"   => $packet->getPacketTransportLayerId(),
          "packet_application_layer_id" => $packet->getPacketApplicationLayerId(),
          "packet_direction_id"         => $packet->getPacketDirectionId(),
          "packet_id"        => $packet->getPacketId(),
          "packet_name"      => $packet->getPacketName(),
          "packet_format"    => $packet->getPacketFormat(),
          "packet_remarks"   => $packet->getPacketRemarks(false),
          "user"             => $user,
          "url" => Common::relativeUrlToAbsolute(
            "/packet/" . $packet->getId() . "/"
            . Common::sanitizeForUrl($packet->getPacketName())
          )
        ];
      }
      $model->packets = $packets;
    }

    // Remove duplicates if non-html and non-json
    if (!$view instanceof PacketIndexHtmlView
      && !$view instanceof PacketIndexJSONView) {
      $packets = [];
      foreach ($model->packets as $pkt) {
        // This removes duplicates by overwriting keys that already exist.
        $packets[$pkt->getPacketId().$pkt->getPacketName()] = $pkt;
      }
      $model->packets = $packets;
    }

    // Post-filter summary of packets
    $model->sum_packets = count($model->packets);

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

  protected static function renderDateTime($obj) {
    if (!$obj instanceof DateTime) return $obj;
    return $obj->format("r");
  }

}
