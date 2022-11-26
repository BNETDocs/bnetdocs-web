<?php

namespace BNETDocs\Views;

class ServersHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Servers)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'Servers'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
