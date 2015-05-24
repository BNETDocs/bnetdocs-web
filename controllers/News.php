<?php

namespace BNETDocs\Controllers;

use BNETDocs\Libraries\Controller;
use BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use BNETDocs\Libraries\Router;

use BNETDocs\Models\News as NewsModel;
use BNETDocs\Views\NewsHtml as NewsHtmlView;

class News extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "":
      case "htm":
      case "html":
        $view = new NewsHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new NewsModel();
    if (extension_loaded("newrelic")) {
      newrelic_add_custom_parameter("model", (new \ReflectionClass($model))->getShortName());
      newrelic_add_custom_parameter("view", (new \ReflectionClass($view))->getShortName());
    }
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseHeader("Cache-Control", "max-age=300");
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseHeader("Expires", (new \DateTime("+300 second"))->setTimezone(new \DateTimeZone("GMT"))->format("D, d M Y H:i:s e"));
    $router->setResponseHeader("Pragma", "max-age=300");
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
