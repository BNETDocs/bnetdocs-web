<?php /* vim: set colorcolumn=: */

namespace BNETDocs\Views\Base;

abstract class Vb implements \BNETDocs\Interfaces\View
{
  public const MIMETYPE_VB = 'text/x-vb';

  /**
   * Provides the MIME-type that this View prints.
   *
   * @return string The MIME-type for this View class.
   */
  public static function mimeType(): string
  {
    return \sprintf('%s;charset=utf-8', self::MIMETYPE_VB);
  }
}
