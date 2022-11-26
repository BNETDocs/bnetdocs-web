<?php

namespace BNETDocs\Views\News;

class EditHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\News\Edit)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'News/Edit'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
