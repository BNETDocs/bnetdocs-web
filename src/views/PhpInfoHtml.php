<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Views;

use \BNETDocs\Models\PhpInfo as PhpInfoModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class PhpInfoHtml extends View
{
  public function getMimeType()
  {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model)
  {
    if (!$model instanceof PhpInfoModel)
    {
      throw new IncorrectModelException();
    }
    (new Template($model, 'PhpInfo'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
