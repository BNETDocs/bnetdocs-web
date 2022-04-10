<?php
namespace BNETDocs\Views;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\PrivacyNotice as PrivacyNoticeModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class PrivacyNoticeHtml extends View
{
  public function getMimeType()
  {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model)
  {
    if (!$model instanceof PrivacyNoticeModel)
    {
      throw new IncorrectModelException();
    }
    (new Template($model, 'PrivacyNotice'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
