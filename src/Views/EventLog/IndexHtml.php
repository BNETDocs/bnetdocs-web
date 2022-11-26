<?php

namespace BNETDocs\Views\EventLog;

class IndexHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\EventLog\Index)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'EventLog/Index'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
