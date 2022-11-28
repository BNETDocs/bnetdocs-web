<?php

namespace BNETDocs\Exceptions;

class UserNotFoundException extends DatabaseObjectNotFoundException
{
  public function __construct(\BNETDocs\Libraries\User|int $value, \Throwable $previous = null)
  {
    $v = is_int($value) ? $value : $value->getId();
    parent::__construct(\sprintf('User not found: %d', $v), 0, $previous);
  }
}
