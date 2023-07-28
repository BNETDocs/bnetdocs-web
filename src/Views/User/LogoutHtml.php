<?php

namespace BNETDocs\Views\User;

class LogoutHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\User\Logout)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'User/Logout'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
