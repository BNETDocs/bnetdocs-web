<?php

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\PacketNotFoundException;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Packet;
use \BNETDocs\Libraries\Product;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Packet\View as PacketViewModel;
use \BNETDocs\Views\Packet\ViewHtml as PacketViewHtmlView;
use \BNETDocs\Views\Packet\ViewPlain as PacketViewPlainView;
use \DateTime;
use \DateTimeZone;

class View extends Controller {

  protected $packet_id;

  public function __construct($packet_id) {
    parent::__construct();
    $this->packet_id = $packet_id;
  }

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new PacketViewHtmlView();
      break;
      case "txt":
        $view = new PacketViewPlainView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new PacketViewModel();
    $model->packet_id    = $this->packet_id;
    try {
      $model->packet     = new Packet($this->packet_id);
    } catch (PacketNotFoundException $e) {
      $model->packet     = null;
    }
    $model->used_by      = $this->getUsedBy($model->packet);
    $model->user_session = UserSession::load($router);
    ob_start();
    $view->render($model);
    $router->setResponseCode(($model->packet ? 200 : 404));
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
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
