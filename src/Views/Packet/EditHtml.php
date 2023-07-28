<?php

namespace BNETDocs\Views\Packet;

class EditHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\Packet\Form)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'Packet/Edit'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
