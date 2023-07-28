<?php

namespace BNETDocs\Views\User;

class ResetPasswordHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\User\ResetPassword)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'User/ResetPassword'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
