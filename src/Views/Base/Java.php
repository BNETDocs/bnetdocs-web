<?php /* vim: set colorcolumn=: */

namespace BNETDocs\Views\Base;

abstract class Java implements \BNETDocs\Interfaces\View
{
  public const MIMETYPE_JAVA = 'text/x-java-source';

  /**
   * Provides the MIME-type that this View prints.
   *
   * @return string The MIME-type for this View class.
   */
  public static function mimeType() : string
  {
    return \sprintf('%s;charset=utf-8', self::MIMETYPE_JAVA);
  }
}
