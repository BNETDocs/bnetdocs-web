<?php

namespace BNETDocs\Views\Analytics;

class DashboardPlain extends \BNETDocs\Views\Base\Plain
{
    public static function invoke(\BNETDocs\Interfaces\Model $model): void
    {
        if (!$model instanceof \BNETDocs\Models\Analytics\Dashboard)
        {
            throw new \BNETDocs\Exceptions\InvalidModelException($model);
        }

        echo $model;
        $model->_responseHeaders['Content-Type'] = self::mimeType();
    }
}
