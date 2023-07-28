<?php

namespace BNETDocs\Views\User;

class IndexHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\User\Index)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'User/Index'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
