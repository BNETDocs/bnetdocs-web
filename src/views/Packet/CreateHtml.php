<?php
namespace BNETDocs\Views\Packet;

use \BNETDocs\Models\Packet\Create as PacketCreateModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class CreateHtml extends View
{
  public function getMimeType()
  {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model)
  {
    if (!$model instanceof PacketCreateModel)
    {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Packet/Create'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
