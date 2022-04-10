<?php
namespace BNETDocs\Views\Packet;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\Packet\Form as FormModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class EditHtml extends View
{
  public function getMimeType()
  {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model)
  {
    if (!$model instanceof FormModel)
    {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Packet/Edit'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
