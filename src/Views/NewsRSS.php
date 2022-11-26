<?php

namespace BNETDocs\Views;

class NewsRSS extends \BNETDocs\Views\Base\RSS
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\News)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    (new \BNETDocs\Libraries\Template($model, 'News.rss'))->invoke();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
