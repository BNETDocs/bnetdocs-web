<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\PhpInfo as PhpInfoModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class PhpInfo extends Controller
{
  public function &run(Router &$router, View &$view, array &$args)
  {
    $model = new PhpInfoModel();
    $model->active_user = Authentication::$user;

    if (!($model->active_user && $model->active_user->getOption(User::OPTION_ACL_PHPINFO)))
    {
      $view->render($model);
      $model->_responseCode = 401;
      return $model;
    }

    ob_start();
    phpinfo(INFO_ALL);
    $model->phpinfo = ob_get_clean();

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
