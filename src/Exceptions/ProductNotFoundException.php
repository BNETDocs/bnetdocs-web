<?php

namespace BNETDocs\Exceptions;

class ProductNotFoundException extends DatabaseObjectNotFoundException
{
  public function __construct(\BNETDocs\Libraries\Product $value, \Throwable $previous = null)
  {
    parent::__construct('Product not found', 0, $previous);
  }
}
