<?php

namespace BNETDocs\Exceptions;

class EventNotFoundException extends DatabaseObjectNotFoundException
{
  public function __construct(\BNETDocs\Libraries\Event|int $value, \Throwable $previous = null)
  {
    $v = is_int($value) ? $value : $value->getId();
    parent::__construct(\sprintf('Event not found: %d', $v), 0, $previous);
    \BNETDocs\Libraries\Logger::logMetric('event_id', $v);
  }
}
