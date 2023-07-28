<?php

namespace BNETDocs\Views\Server;

class DeleteHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\Server\Delete)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'Server/Delete'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
