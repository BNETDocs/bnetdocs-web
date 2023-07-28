<?php

namespace BNETDocs\Views;

class PrivacyNoticeHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\PrivacyNotice)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'PrivacyNotice'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
