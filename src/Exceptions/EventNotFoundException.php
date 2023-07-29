<?php

namespace BNETDocs\Exceptions;

class EventNotFoundException extends DatabaseObjectNotFoundException
{
  public function __construct(\BNETDocs\Libraries\EventLog\Event|int $value, \Throwable $previous = null)
  {
    $v = is_int($value) ? $value : $value->getId();
    parent::__construct(\sprintf('Event log id not found: %d', $v), 0, $previous);
  }
}
