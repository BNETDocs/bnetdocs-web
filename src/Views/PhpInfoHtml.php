<?php

namespace BNETDocs\Views;

class PhpInfoHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\PhpInfo)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'PhpInfo'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
