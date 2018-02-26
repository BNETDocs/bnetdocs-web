<?php

namespace BNETDocs\Libraries;

use \DateTime;

final class ArrayFlattener {

  const DELIMITER = '_';
  const MAX_DEPTH = 4;

  /**
   * __construct()
   * We set this to private so code cannot instantiate this class.
   * This class is entirely static and meant to be used that way.
   */
  private function __construct() {}

  /**
   * flatten()
   * Flattens an array or object into a string.
   *
   * @param $thing The array or object to be flattened.
   * @param $show_all Whether to hide keys beginning with underscores.
   *
   * @return string The flattened thing.
   */
  public static function flatten( &$thing, $show_all = false ) {
    $buffer = '';

    if ( is_object( $thing ) ) {
      $vars = get_object_vars( $thing );
    } else {
      $vars = &$thing;
    }

    foreach ( $vars as $key => $value ) {
      self::__flatten( $buffer, $key, $value, $show_all, 0 );
    }

    return $buffer;
  }

  /**
   * __flatten()
   * Does the grunt work of flatten().
   *
   * @param &$buffer The string buffer that will be returned by flatten().
   * @param &$key The name of the current key being processed.
   * @param &$value The key's value, can be anything.
   * @param &$show_all Whether to hide keys beginning with underscores.
   * @param $depth The current depth level, not to exceed MAX_DEPTH.
   *
   * @return void
   */
  private static function __flatten(
    &$buffer, &$key, &$value, &$show_all, $depth
  ) {
    if ( $depth >= self::MAX_DEPTH ) {
      // exceeded maximum recursion depth
      $buffer .= $key . PHP_EOL;
      return;
    }

    if ( !$show_all && substr( $key, 0, 1 ) == '_' ) {
      // do not show keys beginning with underscores.
      return;
    }

    if ( is_null( $value ) ) {
      $buffer .= $key . ' null' . PHP_EOL;
      return;
    }

    if ( is_bool( $value ) ) {
      $buffer .= $key . ' ' . ( $value ? 'true' : 'false' ) . PHP_EOL;
      return;
    }

    if ( is_scalar( $value ) ) {
      $buffer .= $key . ' ' . (string) $value . PHP_EOL;
      return;
    }

    if ( $value instanceof DateTime ) {
      $buffer .= $key . '_iso '  . $value->format( 'r' ) . PHP_EOL;
      $buffer .= $key . '_unix ' . $value->format( 'U' ) . PHP_EOL;
      return;
    }

    if ( is_object( $value ) ) {
      $keys = get_object_vars( $value );
    } else {
      $keys = &$value;
    }

    if ( empty( $keys ) ) {
      $buffer .= $key . PHP_EOL;
      return;
    }

    foreach ( $keys as $_key => $_value ) {
      $__key = $key . self::DELIMITER . $_key;
      self::__flatten(
        $buffer, $__key, $_value, $show_all, ++$depth
      );
    }
  }

}
