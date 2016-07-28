<?php

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Packet;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Packet\Search as PacketSearchModel;
use \BNETDocs\Views\Packet\SearchHtml as PacketSearchHtmlView;

class Search extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new PacketSearchHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }

    $model = new PacketSearchModel();

    $data = $router->getRequestQueryArray();

    $model->query = (isset($data["q"]) ? (string) $data["q"] : null);

    if (!empty($model->query)) {
      $model->packets = Packet::getAllPacketsBySearch($model->query);
    }

    $model->user_session = UserSession::load($router);

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

    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
