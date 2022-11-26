<?php

namespace BNETDocs\Views;

class LegalPlain extends \BNETDocs\Views\Base\Plain
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Legal)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    echo $model->license;
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
