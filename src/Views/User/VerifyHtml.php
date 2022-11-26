<?php

namespace BNETDocs\Views\User;

class VerifyHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\User\Verify)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'User/Verify'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
