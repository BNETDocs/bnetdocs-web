<?php

namespace BNETDocs\Models\Packet;

class View extends \BNETDocs\Models\ActiveUser
{
  public ?array $comments = null;
  public ?\BNETDocs\Libraries\Packet $packet = null;
  public ?int $packet_id = null;
  public ?array $used_by = null;
}
