<?php

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Packet;
use \BNETDocs\Models\Packet\Search as PacketSearchModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Search extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $data         = $router->getRequestQueryArray();
    $model        = new PacketSearchModel();
    $model->query = (isset($data["q"]) ? (string) $data["q"] : null);

    if (!empty($model->query)) {
      $model->packets = Packet::getAllPacketsBySearch($model->query);
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

    if ($model->packets) {
      $model->sum_packets = count($model->packets);
    }

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
