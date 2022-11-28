<?php /* vim: set colorcolumn=: */

namespace BNETDocs\Views\Base;

abstract class Json implements \BNETDocs\Interfaces\View
{
  public const MIMETYPE_JSON = 'application/json';

  /**
   * Gets the standard flags to call with json_encode() in subclasses.
   *
   * @return integer The flags to pass to json_encode().
   */
  public static function jsonFlags() : int
  {
    return \JSON_PRESERVE_ZERO_FRACTION
      | \JSON_THROW_ON_ERROR
      | (\php_sapi_name() == 'cli' || \CarlBennett\MVC\Libraries\Common::isBrowser(\getenv('HTTP_USER_AGENT')) ? \JSON_PRETTY_PRINT : 0);
  }

  /**
   * Provides the MIME-type that this View prints.
   *
   * @return string The MIME-type for this View class.
   */
  public static function mimeType() : string
  {
    return \sprintf('%s;charset=utf-8', self::MIMETYPE_JSON);
  }
}
