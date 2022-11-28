<?php

namespace BNETDocs\Exceptions;

class CommentNotFoundException extends DatabaseObjectNotFoundException
{
  public function __construct(\BNETDocs\Libraries\Comment|int $value, \Throwable $previous = null)
  {
    $v = is_int($value) ? $value : $value->getId();
    parent::__construct(\sprintf('Comment not found: %d', $v), 0, $previous);
  }
}
