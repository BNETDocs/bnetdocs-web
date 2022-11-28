<?php

namespace BNETDocs\Exceptions;

class DocumentNotFoundException extends DatabaseObjectNotFoundException
{
  public function __construct(\BNETDocs\Libraries\Document|int $value, \Throwable $previous = null)
  {
    $v = is_int($value) ? $value : $value->getId();
    parent::__construct(\sprintf('Document not found: %d', $v), 0, $previous);
  }
}
