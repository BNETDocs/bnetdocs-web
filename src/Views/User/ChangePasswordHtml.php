<?php

namespace BNETDocs\Views\User;

class ChangePasswordHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\User\ChangePassword)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'User/ChangePassword'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
