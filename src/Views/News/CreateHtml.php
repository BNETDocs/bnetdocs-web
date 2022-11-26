<?php

namespace BNETDocs\Views\News;

class CreateHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\News\Create)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'News/Create'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
