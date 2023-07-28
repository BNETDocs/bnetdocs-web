<?php

namespace BNETDocs\Views;

class DonateHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\Donate)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'Donate'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
