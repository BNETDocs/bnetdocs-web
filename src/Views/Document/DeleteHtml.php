<?php

namespace BNETDocs\Views\Document;

class DeleteHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Document\Delete)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'Document/Delete'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
