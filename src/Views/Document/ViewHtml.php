<?php

namespace BNETDocs\Views\Document;

class ViewHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Document\View)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'Document/View'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
