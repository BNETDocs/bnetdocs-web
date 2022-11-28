<?php

namespace BNETDocs\Exceptions;

class ControllerNotFoundException extends \InvalidArgumentException
{
  public function __construct(string $value, \Throwable $previous = null)
  {
    parent::__construct(\sprintf('Controller not found: %s', $value), 0, $previous);
  }
}
