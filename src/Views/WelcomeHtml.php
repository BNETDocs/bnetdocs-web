<?php

namespace BNETDocs\Views;

class WelcomeHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Welcome)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'Welcome'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
