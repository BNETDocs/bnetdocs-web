<?php

namespace BNETDocs\Views;

class LegacyHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\Legacy)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'Legacy'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
