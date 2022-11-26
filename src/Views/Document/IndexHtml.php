<?php

namespace BNETDocs\Views\Document;

class IndexHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Document\Index)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'Document/Index'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
