<?php

namespace BNETDocs\Exceptions;

class ServerNotFoundException extends DatabaseObjectNotFoundException
{
  public function __construct(\BNETDocs\Libraries\Server|int $value, \Throwable $previous = null)
  {
    $v = is_int($value) ? $value : $value->getId();
    parent::__construct(\sprintf('SErver not found: %d', $v), 0, $previous);
    \BNETDocs\Libraries\Logger::logMetric('server_id', $v);
  }
}
