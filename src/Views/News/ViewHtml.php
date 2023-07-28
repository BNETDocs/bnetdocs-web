<?php

namespace BNETDocs\Views\News;

class ViewHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\News\View)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'News/View'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
