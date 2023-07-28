<?php

namespace BNETDocs\Views\Server;

class ViewHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\Server\View)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'Server/View'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
