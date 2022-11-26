<?php

namespace BNETDocs\Views\EventLog;

class IndexJson extends \BNETDocs\Views\Base\Json
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\EventLog\Index)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    echo \json_encode(['event_log' => $model->event_log], self::jsonFlags());
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
