<?php

namespace BNETDocs\Exceptions;

class NewsCategoryNotFoundException extends DatabaseObjectNotFoundException
{
  public function __construct(\BNETDocs\Libraries\NewsCategory|int $value, \Throwable $previous = null)
  {
    $v = is_int($value) ? $value : $value->getId();
    parent::__construct(\sprintf('News Category not found: %d', $v), 0, $previous);
  }
}
