<?php

namespace BNETDocs\Views\Packet;

class ViewJson extends \BNETDocs\Views\Base\Json
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Packet\View)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    echo json_encode(['comments' => $model->comments, 'packet' => $model->packet], self::jsonFlags());
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
