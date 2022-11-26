<?php

namespace BNETDocs\Views\Document;

class EditHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Document\Edit)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'Document/Edit'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
