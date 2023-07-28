<?php

namespace BNETDocs\Views\Packet;

class DeleteHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\Packet\Delete)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'Packet/Delete'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
