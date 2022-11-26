<?php

namespace BNETDocs\Views;

class ServersJson extends \BNETDocs\Views\Base\Json
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Servers)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    $content = [];

    foreach ($model->server_types as $server_type) {
      $content['server_types'][] = [
        'id'    => (int) $server_type->getId(),
        'label' =>       $server_type->getLabel()
      ];
    }

    foreach ($model->servers as $server) {
      $content['servers'][] = $server;
    }

    $content['status_bitmasks'] = $model->status_bitmasks;

    echo json_encode($content, self::jsonFlags());
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
