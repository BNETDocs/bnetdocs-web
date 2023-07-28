<?php

namespace BNETDocs\Views\Document;

class ViewJson extends \BNETDocs\Views\Base\Json
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\Document\View)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    echo json_encode(['comments' => $model->comments, 'document' => $model->document], self::jsonFlags());
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
