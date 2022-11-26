<?php

namespace BNETDocs\Views;

class PageNotFoundJson extends \BNETDocs\Views\Base\Json
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\PageNotFound)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    echo \json_encode(['error' => [
      $model->_responseCode, 'Page Not Found', 'The requested resource does not exist or could not be found.'
    ]]);
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
