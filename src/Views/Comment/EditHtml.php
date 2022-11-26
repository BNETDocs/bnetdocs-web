<?php

namespace BNETDocs\Views\Comment;

class EditHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Comment\Edit)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'Comment/Edit'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
