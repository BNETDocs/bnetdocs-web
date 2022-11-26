<?php

namespace BNETDocs\Views\User;

class LoginHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\User\Login)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'User/Login'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
