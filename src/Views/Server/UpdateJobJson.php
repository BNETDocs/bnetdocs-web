<?php

namespace BNETDocs\Views\Server;

class UpdateJobJson extends \BNETDocs\Views\Base\Json
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Server\UpdateJob)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    echo \json_encode($model, self::jsonFlags());
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
