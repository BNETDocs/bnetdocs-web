<?php

namespace BNETDocs\Views\User;

class CreatePasswordHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\User\CreatePassword)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'User/CreatePassword'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
