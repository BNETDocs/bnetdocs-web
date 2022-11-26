<?php

namespace BNETDocs\Views;

class DiscordHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Discord)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'Discord'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
