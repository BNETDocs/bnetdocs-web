<?php

namespace BNETDocs\Views\Packet;

class IndexJson extends \BNETDocs\Views\Base\Json
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Packet\Index)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    echo json_encode(['packets' => $model->packets], self::jsonFlags());
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
