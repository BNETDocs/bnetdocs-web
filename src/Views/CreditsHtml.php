<?php

namespace BNETDocs\Views;

class CreditsHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Credits)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'Credits'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
