<?php

namespace BNETDocs\Models\Packet;

class Delete extends \BNETDocs\Models\ActiveUser
{
  public bool $acl_allowed = false;
  public ?int $id = null;
  public ?\BNETDocs\Libraries\Packet $packet = null;
  public ?string $title = null;
}
