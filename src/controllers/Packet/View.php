<?php

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Controllers\Redirect as RedirectController;
use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Exceptions\PacketNotFoundException;
use \BNETDocs\Libraries\Packet;
use \BNETDocs\Libraries\Product;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Packet\View as PacketViewModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \DateTime;
use \DateTimeZone;

class View extends Controller {

  protected $packet_id;

  public function __construct($packet_id) {
    parent::__construct();
    $this->packet_id = $packet_id;
  }

  public function &run(Router &$router, View &$view, array &$args) {

    $model            = new PacketViewModel();
    $model->packet_id = (int) $this->packet_id;

    try {
      $model->packet  = new Packet($this->packet_id);
    } catch (PacketNotFoundException $e) {
      $model->packet  = null;
    }

    $pathArray = $router->getRequestPathArray();

    if ($model->packet && (
      !isset($pathArray[3]) || empty($pathArray[3]))) {
      $redirect = new RedirectController(
        Common::relativeUrlToAbsolute(
          "/packet/" . $model->packet->getId() . "/"
          . Common::sanitizeForUrl(
            $model->packet->getPacketName(), true
          )
        ), 302
      );
      return $redirect->run($router);
    }

    if ($model->packet) {
      $model->comments = Comment::getAll(
        Comment::PARENT_TYPE_PACKET,
        $model->packet_id
      );
      $model->used_by = $this->getUsedBy($model->packet);
    } else {
      $model->used_by = null;
    }

    $model->user_session = UserSession::load($router);

    $view->render($model);

    $model->_responseCode = ($model->packet ? 200 : 404);
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

  protected function getUsedBy(Packet &$packet) {
    if (is_null($packet)) return null;
    $used_by = $packet->getUsedBy();
    $products = [];
    foreach ($used_by as $bnet_product_id) {
      $products[] = new Product($bnet_product_id);
    }
    return $products;
  }

}
