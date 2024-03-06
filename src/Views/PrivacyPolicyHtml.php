<?php

namespace BNETDocs\Views;

class PrivacyPolicyHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\PrivacyPolicy)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    (new \BNETDocs\Libraries\Template($model, 'PrivacyPolicy'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
