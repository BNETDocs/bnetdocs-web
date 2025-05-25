<?php

namespace BNETDocs\Views\Analytics;

class DashboardHtml extends \BNETDocs\Views\Base\Html
{
    public static function invoke(\BNETDocs\Interfaces\Model $model): void
    {
        if (!$model instanceof \BNETDocs\Models\Analytics\Dashboard)
        {
            throw new \BNETDocs\Exceptions\InvalidModelException($model);
        }

        (new \BNETDocs\Libraries\Core\Template($model, 'Analytics/Dashboard'))->invoke();
        $model->_responseHeaders['Content-Type'] = self::mimeType();
    }
}
