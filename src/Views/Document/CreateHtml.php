<?php

namespace BNETDocs\Views\Document;

class CreateHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\Document\Create)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'Document/Create'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
