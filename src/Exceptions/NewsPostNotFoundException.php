<?php

namespace BNETDocs\Exceptions;

class NewsPostNotFoundException extends DatabaseObjectNotFoundException
{
  public function __construct(\BNETDocs\Libraries\News\Post|int $value, ?\Throwable $previous = null)
  {
    $v = is_int($value) ? $value : $value->getId();
    parent::__construct(\sprintf('News Post not found: %d', $v), 0, $previous);
  }
}
