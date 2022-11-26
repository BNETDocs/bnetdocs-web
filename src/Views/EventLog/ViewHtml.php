<?php

namespace BNETDocs\Views\EventLog;

class ViewHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\EventLog\View)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'EventLog/View'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
