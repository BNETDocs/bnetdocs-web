<?php

namespace BNETDocs\Models\EventLog;

class View extends \BNETDocs\Models\ActiveUser
{
  public bool $acl_allowed = false;
  public ?\BNETDocs\Libraries\EventLog\Event $event = null;
  public ?int $id = null;
}
