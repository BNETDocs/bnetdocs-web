<?php

namespace BNETDocs\Views\User;

class RegisterHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\User\Register)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'User/Register'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
