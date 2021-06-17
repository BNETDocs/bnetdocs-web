<?php /* vim: set colorcolumn=: */
namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Models\PrivacyNotice as PrivacyNoticeModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class PrivacyNotice extends Controller
{
  public function &run(Router &$router, View &$view, array &$args)
  {
    $model = new PrivacyNoticeModel();
    $model->data_location = Common::$config->bnetdocs->privacy->data_location;
    $model->email_domain = common::$config->bnetdocs->privacy->contact->email_domain;
    $model->email_mailbox = common::$config->bnetdocs->privacy->contact->email_mailbox;
    $model->organization = Common::$config->bnetdocs->privacy->organization;
    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
