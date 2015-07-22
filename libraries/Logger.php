<?php

namespace BNETDocs\Libraries;

use \Exception;
use \ReflectionExtension;

class Logger {

  protected static $newrelic_available = false;

  /**
   * This constructor is private because Logger is entirely static.
   *
   * This will cause errors if instantiation is attempted.
   */
  private function __construct() {}

  public static function getTraceString() {
    ob_start();
    debug_print_backtrace();
    return ob_get_clean();
  }

  public static function initialize() {
    if (extension_loaded("newrelic")) {
      newrelic_disable_autorum();
      self::$newrelic_available = true;
      self::setTransactionName("null");
      self::logMetric("REMOTE_ADDR", getenv("REMOTE_ADDR"));
    }
  }

  public static function logError($no, $str, $file, $line, $obj) {
    if (self::$newrelic_available) {
      newrelic_notice_error($no, $str, $file, $line, $obj);
    }
  }

  public static function logException(Exception $exception) {
    if (self::$newrelic_available) {
      newrelic_notice_error($exception->getMessage(), $exception);
    }
  }

  public static function logMetric($key, $val) {
    if (self::$newrelic_available) {
      newrelic_add_custom_parameter($key, $val);
    }
  }

  public static function setTransactionName($name) {
    if (self::$newrelic_available) {
      newrelic_name_transaction($name);
    }
  }

}
