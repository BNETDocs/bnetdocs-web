<?php
namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\VersionInfo;
use \BNETDocs\Models\Legal as LegalModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \DateTime;
use \DateTimeZone;

class Legal extends Controller {

  const LICENSE_FILE = '../LICENSE.txt';

  public function &run(Router &$router, View &$view, array &$args) {
    $model                  = new LegalModel();
    $model->email_domain    = Common::$config->bnetdocs->privacy->contact->email_domain;
    $model->email_mailbox   = Common::$config->bnetdocs->privacy->contact->email_mailbox;
    $model->license         = file_get_contents(self::LICENSE_FILE);
    $model->license_version = VersionInfo::$version->bnetdocs[3] ?? null;

    if (!is_null($model->license_version)) {
      $model->license_version = explode( ' ', $model->license_version );
      $model->license_version[1] = new DateTime(
        $model->license_version[1], new DateTimeZone( 'Etc/UTC' )
      );
    } else {
      $model->license_version = array();
      $model->license_version[0] = null;
      $model->license_version[1] = new DateTime(
        '@' . filemtime(self::LICENSE_FILE), new DateTimeZone( 'Etc/Utc' )
      );
    }

    $view->render( $model );
    $model->_responseCode = 200;
    return $model;
  }
}
