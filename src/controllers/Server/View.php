<?php

namespace BNETDocs\Controllers\Server;

use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\ServerNotFoundException;
use \BNETDocs\Libraries\Exceptions\ServerTypeNotFoundException;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Packet;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\Server;
use \BNETDocs\Libraries\ServerType;
use \BNETDocs\Models\Server\View as ServerViewModel;
use \BNETDocs\Views\Server\ViewHtml as ServerViewHtmlView;
use \CarlBennett\MVC\Libraries\Common;
use \DateTime;
use \DateTimeZone;

class View extends Controller {

  protected $server_id;

  public function __construct($server_id) {
    parent::__construct();
    $this->server_id = $server_id;
  }

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new ServerViewHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new ServerViewModel();

    $model->server_id = $this->server_id;

    try {
      $model->server      = new Server($this->server_id);
      $model->server_type = new ServerType($model->server->getTypeId());
    } catch (ServerNotFoundException $e) {
      $model->server = null;
    } catch (ServerTypeNotFoundException $e) {
      $model->server_type = null;
    }

    ob_start();
    $view->render($model);
    $router->setResponseCode(($model->server ? 200 : 404));
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
