<?php

namespace BNETDocs\Views\Comment;

class DeleteHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Comment\Delete)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'Comment/Delete'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
