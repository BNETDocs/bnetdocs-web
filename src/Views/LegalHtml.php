<?php

namespace BNETDocs\Views;

class LegalHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\Legal)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'Legal'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
