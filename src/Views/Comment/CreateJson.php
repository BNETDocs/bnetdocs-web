<?php

namespace BNETDocs\Views\Comment;

class CreateJson extends \BNETDocs\Views\Base\Json
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\Comment\Create)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    echo \json_encode($model->response, self::jsonFlags());
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
