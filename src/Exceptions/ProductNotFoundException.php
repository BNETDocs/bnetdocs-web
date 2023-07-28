<?php

namespace BNETDocs\Exceptions;

class ProductNotFoundException extends DatabaseObjectNotFoundException
{
  public function __construct(\BNETDocs\Libraries\Product|int $value, \Throwable $previous = null)
  {
    $v = is_int($value) ? $value : $value->getBnetProductId();
    parent::__construct(\sprintf('Product not found: %d', $v), 0, $previous);
  }
}
