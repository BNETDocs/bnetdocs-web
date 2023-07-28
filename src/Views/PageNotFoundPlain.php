<?php

namespace BNETDocs\Views;

class PageNotFoundPlain extends \BNETDocs\Views\Base\Plain
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\PageNotFound)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    echo "Page Not Found\r\nThe requested resource does not exist or could not be found.\r\n";
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
