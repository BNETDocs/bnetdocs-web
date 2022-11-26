<?php /* vim: set colorcolumn=: */

namespace BNETDocs\Controllers;

abstract class Base implements \BNETDocs\Interfaces\Controller
{
  /**
   * The Model to be set by subclasses and used by a View.
   *
   * @var \BNETDocs\Interfaces\Model|null
   */
  public ?\BNETDocs\Interfaces\Model $model = null;
}
