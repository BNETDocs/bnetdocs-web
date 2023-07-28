<?php

namespace BNETDocs\Views\Document;

class IndexJson extends \BNETDocs\Views\Base\Json
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\Document\Index)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    echo json_encode(['documents' => $model->documents], self::jsonFlags());
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
