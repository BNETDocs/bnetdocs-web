<?php

namespace BNETDocs\Views\Server;

class ViewJson extends \BNETDocs\Views\Base\Json
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Server\View)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    echo json_encode(['server' => $model->server], self::jsonFlags());
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
