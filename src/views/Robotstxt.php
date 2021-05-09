<?php
namespace BNETDocs\Views;

use \BNETDocs\Models\Robotstxt as RobotstxtModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class Robotstxt extends View
{
  public function getMimeType()
  {
    return 'text/plain;charset=utf-8';
  }

  public function render(Model &$model)
  {
    if (!$model instanceof RobotstxtModel)
    {
      throw new IncorrectModelException();
    }
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();

    foreach ($model->rules as $useragent => $rules)
    {
      printf("User-agent: %s\r\n", $useragent);

      foreach ($rules as $rule)
      {
        foreach ($rule as $action => $url)
        {
          printf("%s: %s\r\n", $action, $url);
        }
      }
    }
  }
}
