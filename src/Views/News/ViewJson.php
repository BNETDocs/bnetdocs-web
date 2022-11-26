<?php

namespace BNETDocs\Views\News;

class ViewJson extends \BNETDocs\Views\Base\Json
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\News\View)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    echo \json_encode(['comments' => $model->comments, 'news_post' => $model->news_post], self::jsonFlags());
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
