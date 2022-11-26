<?php

namespace BNETDocs\Views\Packet;

class ViewHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Packet\View)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'Packet/View'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
