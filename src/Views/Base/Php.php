<?php /* vim: set colorcolumn=: */

namespace BNETDocs\Views\Base;

abstract class Php implements \BNETDocs\Interfaces\View
{
  public const MIMETYPE_PHP = 'text/x-php';

  /**
   * Provides the MIME-type that this View prints.
   *
   * @return string The MIME-type for this View class.
   */
  public static function mimeType(): string
  {
    // There isn't an assigned MIME-type from IANA.
    // <https://cweiske.de/tagebuch/php-mimetype.htm>
    return \sprintf('%s;charset=utf-8', self::MIMETYPE_PHP);
  }
}
