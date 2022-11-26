<?php

namespace BNETDocs\Views\User;

class ViewJson extends \BNETDocs\Views\Base\Json
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\User\View)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    echo \json_encode($model, self::jsonFlags());
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
