<?php

namespace BNETDocs\Views;

class MaintenanceHtml extends \BNETDocs\Views\Base\Html
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Maintenance)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'Maintenance'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
