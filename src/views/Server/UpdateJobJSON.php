<?php

namespace BNETDocs\Views\Server;

use \BNETDocs\Models\Server\UpdateJob as UpdateJobModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class UpdateJobJSON extends View {

  public function getMimeType() {
    return 'application/json;charset=utf-8';
  }

  public function render( Model &$model ) {
    if ( !$model instanceof UpdateJobModel ) {
      throw new IncorrectModelException();
    }

    echo json_encode( $model, Common::prettyJSONIfBrowser() );
  }

}
