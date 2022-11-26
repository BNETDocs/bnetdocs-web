<?php

namespace BNETDocs\Views\Packet;

class IndexHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Packet\Index)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'Packet/Index'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
