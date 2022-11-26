<?php

namespace BNETDocs\Views\User;

class IndexJson extends \BNETDocs\Views\Base\Json
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\User\Index)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    echo json_encode(['users' => $model->users], self::jsonFlags());
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
