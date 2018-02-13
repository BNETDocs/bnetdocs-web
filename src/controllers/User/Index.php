<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\Index as UserIndexModel;
use \BNETDocs\Views\User\IndexHtml as UserIndexHtmlView;
use \BNETDocs\Views\User\IndexJSON as UserIndexJSONView;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Gravatar;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Index extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model        = new UserIndexModel();
    $model->users = User::getAllUsers(false);

    // Post-filter summary of users
    $model->sum_users = count($model->users);

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders['Content-Type'] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
