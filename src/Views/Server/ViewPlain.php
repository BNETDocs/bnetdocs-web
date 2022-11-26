<?php

namespace BNETDocs\Views\Server;

class ViewPlain extends \BNETDocs\Views\Base\Plain
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Server\View)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    $model->_responseHeaders['Content-Type'] = self::mimeType();
    if (!$model->server) return;

    $json = $model->server->jsonSerialize();
    echo \BNETDocs\Libraries\ArrayFlattener::flatten($json);
  }
}
