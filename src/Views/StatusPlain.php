<?php

namespace BNETDocs\Views;

class StatusPlain extends \BNETDocs\Views\Base\Plain
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Status)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    echo \BNETDocs\Libraries\ArrayFlattener::flatten($model->status);
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
