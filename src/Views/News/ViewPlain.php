<?php

namespace BNETDocs\Views\News;

class ViewPlain extends \BNETDocs\Views\Base\Plain
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\News\View)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    $model->_responseHeaders['Content-Type'] = self::mimeType();
    echo $model->news_post->getTitle() . "\n";
    echo \str_repeat('=', \strlen($model->news_post->getTitle())) . "\n\n";
    echo $model->news_post->getContent(false);
  }
}
