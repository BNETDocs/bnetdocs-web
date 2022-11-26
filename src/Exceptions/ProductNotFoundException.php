<?php

namespace BNETDocs\Exceptions;

class ProductNotFoundException extends DatabaseObjectNotFoundException
{
  public function __construct(\BNETDocs\Libraries\Product $value, \Throwable $previous = null)
  {
    parent::__construct('Product not found', 0, $previous);
    \BNETDocs\Libraries\Logger::logMetric('product_bnet_id', $value->getBnetProductId());
    \BNETDocs\Libraries\Logger::logMetric('product_bnls_id', $value->getBnlsProductId());
    \BNETDocs\Libraries\Logger::logMetric('product_label', $value->getLabel());
  }
}
