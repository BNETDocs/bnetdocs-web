<?php

namespace BNETDocs\Views\User;

class ViewHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\User\View)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'User/View'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
