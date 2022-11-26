<?php

namespace BNETDocs\Libraries;

use \DateTimeInterface;

class ArrayFlattener
{
  public const DELIMITER = '_';
  public const MAX_DEPTH = 30;

  /**
   * Constructor is set private to prevent objects of this static class.
   */
  private function __construct()
  {
    throw new \LogicException('This static class cannot be constructed');
  }

  /**
   * Flattens an array or object into a string.
   *
   * @param iterable|object $value The array or object to be flattened.
   * @return string The flattened value.
   */
  public static function flatten(iterable|object &$value) : string
  {
    $buffer = '';
    $vars = \is_object($value) ? \get_object_vars($value) : $value;
    foreach ($vars as $k => $v) self::__flatten($buffer, $k, $v, 0);
    return $buffer;
  }

  /**
   * Does the grunt work of flatten().
   *
   * @param &$buffer The string buffer that will be returned by flatten().
   * @param &$key The name of the current key being processed.
   * @param &$value The key's value, can be anything.
   * @param $depth The current depth level, not to exceed MAX_DEPTH.
   * @return void
   */
  private static function __flatten(string &$buffer, mixed &$key, mixed &$value, int $depth) : void
  {
    if ($depth >= self::MAX_DEPTH)
    {
      $buffer .= $key . \PHP_EOL; // Excessive recursion depth, terminate limb
    }
    else if (\is_null($value))
    {
      $buffer .= $key . ' null' . \PHP_EOL;
    }
    else if (\is_bool($value))
    {
      $buffer .= $key . ' ' . ($value ? 'true' : 'false') . \PHP_EOL;
    }
    else if (\is_scalar($value))
    {
      $buffer .= $key . ' ' . (string) $value . \PHP_EOL;
    }
    else if ($value instanceof DateTimeInterface)
    {
      $buffer .= $key . '_iso '  . $value->format(DateTimeInterface::RFC2822) . \PHP_EOL;
      $buffer .= $key . '_tz '  . $value->format('e') . \PHP_EOL;
      $buffer .= $key . '_unix ' . $value->format('U') . \PHP_EOL;
    }
    else if (\is_iterable($value))
    {
      $keys = \is_object($value) ? \get_object_vars($value) : $value;
      if (empty($keys))
      {
        $buffer .= $key . \PHP_EOL;
      }
      else
      {
        foreach ($keys as $_key => $_value) 
        {
          $__key = $key . self::DELIMITER . $_key;
          self::__flatten($buffer, $__key, $_value, $depth++);
        }
      }
    }
  }
}
