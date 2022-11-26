<?php

namespace BNETDocs\Views\News;

class DeleteHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\News\Delete)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'News/Delete'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
