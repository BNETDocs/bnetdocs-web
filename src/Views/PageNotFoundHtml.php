<?php

namespace BNETDocs\Views;

class PageNotFoundHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\PageNotFound)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'PageNotFound'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
