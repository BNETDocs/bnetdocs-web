<?php

namespace BNETDocs\Views\Search;

class SearchPlain extends \BNETDocs\Views\Base\Plain
{
    public static function invoke(\BNETDocs\Interfaces\Model $model): void
    {
        if (!$model instanceof \BNETDocs\Models\Search\Search)
        {
            throw new \BNETDocs\Exceptions\InvalidModelException($model);
        }

        echo 'TODO';
        $model->_responseHeaders['Content-Type'] = self::mimeType();
    }
}
