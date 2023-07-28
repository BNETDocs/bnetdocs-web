<?php

namespace BNETDocs\Views;

class StatusJson extends \BNETDocs\Views\Base\Json
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\Status)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    echo json_encode($model->status, self::jsonFlags());
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
