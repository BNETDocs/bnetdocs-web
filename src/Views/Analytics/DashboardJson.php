<?php

namespace BNETDocs\Views\Analytics;

class DashboardJson extends \BNETDocs\Views\Base\Json
{
    public static function invoke(\BNETDocs\Interfaces\Model $model): void
    {
        if (!$model instanceof \BNETDocs\Models\Analytics\Dashboard)
        {
            throw new \BNETDocs\Exceptions\InvalidModelException($model);
        }

        echo \json_encode($model, self::jsonFlags());
        $model->_responseHeaders['Content-Type'] = self::mimeType();
    }
}
