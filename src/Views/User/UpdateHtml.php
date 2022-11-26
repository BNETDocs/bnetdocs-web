<?php

namespace BNETDocs\Views\User;

class UpdateHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\User\Update)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'User/Update'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
