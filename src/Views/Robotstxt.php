<?php

namespace BNETDocs\Views;

class Robotstxt extends \BNETDocs\Views\Base\Plain
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Robotstxt)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    foreach ($model->rules as $useragent => $rules)
    {
      printf("User-agent: %s\r\n", $useragent);
      foreach ($rules as $rule)
        foreach ($rule as $action => $url)
          printf("%s: %s\r\n", $action, $url);
    }

    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
