<?php

namespace BNETDocs\Views\User;

class EditHtml extends \BNETDocs\Views\Base\Html
{
    public static function invoke(\BNETDocs\Interfaces\Model $model): void
    {
        if (!$model instanceof \BNETDocs\Models\User\Edit)
        {
            throw new \BNETDocs\Exceptions\InvalidModelException($model);
        }

        (new \BNETDocs\Libraries\Core\Template($model, 'User/Edit'))->invoke();
        $model->_responseHeaders['Content-Type'] = self::mimeType();
    }
}
