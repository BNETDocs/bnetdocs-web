<?php

namespace BNETDocs\Views\Search;

class SearchHtml extends \BNETDocs\Views\Base\Html
{
    public static function invoke(\BNETDocs\Interfaces\Model $model): void
    {
        if (!$model instanceof \BNETDocs\Models\Search\Search)
        {
            throw new \BNETDocs\Exceptions\InvalidModelException($model);
        }

        (new \BNETDocs\Libraries\Core\Template($model, 'Search/Search'))->invoke();
        $model->_responseHeaders['Content-Type'] = self::mimeType();
    }
}
