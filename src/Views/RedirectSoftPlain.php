<?php

namespace BNETDocs\Views;

class RedirectSoftPlain extends \BNETDocs\Views\Base\Plain
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\RedirectSoft)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    printf("Redirect: %s\r\n", $model->location);
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
