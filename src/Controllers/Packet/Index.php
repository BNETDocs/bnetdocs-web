<?php

namespace BNETDocs\Controllers\Packet;

class Index extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Packet\Index();
  }

  public function invoke(?array $args): bool
  {
    if (is_null($args) || count($args) != 1)
      throw new \InvalidArgumentException('Arguments must have exactly 1 item');

    $q = \BNETDocs\Libraries\Router::query();
    $this->model->order = isset($q['order']) ? $q['order'] : 'packet-id-asc';
    $this->model->pktapplayer = isset($q['pktapplayer']) ? $q['pktapplayer'] : [];

    switch ($this->model->order)
    {
      case 'created-datetime-asc': $this->model->order = ['created_datetime','ASC']; break;
      case 'created-datetime-desc': $this->model->order = ['created_datetime','DESC']; break;
      case 'id-asc': $this->model->order = ['id','ASC']; break;
      case 'id-desc': $this->model->order = ['id','DESC']; break;
      case 'packet-id-asc': $this->model->order = ['packet_application_layer_id,packet_id','ASC']; break;
      case 'packet-id-desc': $this->model->order = ['packet_application_layer_id,packet_id','DESC']; break;
      case 'user-id-asc': $this->model->order = ['user_id','ASC']; break;
      case 'user-id-desc': $this->model->order = ['user_id','DESC']; break;
      default: $this->model->order = null;
    }

    $this->model->application_layers = \BNETDocs\Libraries\Packet\Application::getAllAsObjects();

    if (empty($this->model->pktapplayer))
    {
      foreach ($this->model->application_layers as $layer)
      {
        $this->model->pktapplayer[] = $layer->getId();
      }
    }

    $this->model->packets = \BNETDocs\Libraries\Packet::getAllPackets(
      '`packet_application_layer_id` IN (' . implode( ',', $this->model->pktapplayer ) . ')',
      $this->model->order
    );

    $deduplicate = (bool) array_shift($args);
    if ($deduplicate)
    {
      $r = [];
      foreach ($this->model->packets as $item) $r[$item->getLabel()] = $item;
      $this->model->packets = $r;
    }

    $this->model->timestamp = new \BNETDocs\Libraries\DateTimeImmutable('now');
    $this->model->_responseCode = 200;
    return true;
  }
}
