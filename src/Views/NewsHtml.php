<?php

namespace BNETDocs\Views;

class NewsHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\News)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'News'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
