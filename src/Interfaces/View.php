<?php /* vim: set colorcolumn=: */

namespace BNETDocs\Interfaces;

interface View
{
  /**
   * Invoked by the Controller class to print the result of a request.
   *
   * @param \BNETDocs\Interfaces\Model $model The object that implements the Model interface.
   * @return void
   */
  public static function invoke(\BNETDocs\Interfaces\Model $model): void;

  /**
   * Provides the MIME-type that this View prints.
   *
   * @return string The MIME-type for this View class.
   */
  public static function mimeType(): string;
}
