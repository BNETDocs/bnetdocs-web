<?php

namespace BNETDocs\Views;

use \BNETDocs\Models\Discord as DiscordModel;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class DiscordHtml extends View {

  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render( Model &$model ) {
    if ( !$model instanceof DiscordModel ) {
      throw new IncorrectModelException();
    }
    ( new Template( $model, 'Discord' ))->render();
  }

}
