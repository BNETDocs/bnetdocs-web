<?php /* vim: set colorcolumn=: */

namespace BNETDocs\Views\Base;

abstract class Cpp implements \BNETDocs\Interfaces\View
{
  public const MIMETYPE_CPP = 'text/x-c';

  /**
   * Provides the MIME-type that this View prints.
   *
   * @return string The MIME-type for this View class.
   */
  public static function mimeType() : string
  {
    return \sprintf('%s;charset=utf-8', self::MIMETYPE_CPP);
  }
}
