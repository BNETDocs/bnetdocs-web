<?php

namespace BNETDocs\Exceptions;

class PacketNotFoundException extends DatabaseObjectNotFoundException
{
  public function __construct(\BNETDocs\Libraries\Packet|int $value, \Throwable $previous = null)
  {
    $v = is_int($value) ? $value : $value->getId();
    parent::__construct(\sprintf('Packet not found: %d', $v), 0, $previous);
  }
}
