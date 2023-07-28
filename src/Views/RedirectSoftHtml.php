<?php

namespace BNETDocs\Views;

class RedirectSoftHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\RedirectSoft)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'RedirectSoft'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
