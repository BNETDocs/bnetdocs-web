<?php

namespace BNETDocs\Libraries;

use \CarlBennett\MVC\Libraries\Common;
use \Exception;
use \StdClass;

final class GlobalErrorHandler {

  /**
   * Do not allow creating an object of this class. It's meant to be 100%
   * static. The way we enforce this rule is by declaring our constructor
   * as private.
   */
  private function __construct() {}

  public static function createOverrides() {
    set_error_handler(
      "\\BNETDocs\\Libraries\\GlobalErrorHandler::errorHandler"
    );
    set_exception_handler(
      "\\BNETDocs\\Libraries\\GlobalErrorHandler::exceptionHandler"
    );
  }

  public static function errorHandler(
    $errno, $errstr, $errfile, $errline, $errcontext
  ) {
    // Don't handle this error if it's turned off administratively:
    if (!(error_reporting() & $errno)) return false;

    // Back out of any output buffers:
    while (ob_get_level()) ob_end_clean();

    // Determine error name from $errno:
    if (class_exists("\\BNETDocs\\Libraries\\Common")) {
      $_errno = Common::phpErrorName($errno);
    } else {
      // Utility function inaccessible, use raw code:
      $_errno = $errno;
    }

    // Create a context object:
    $context             = new StdClass();
    $context->errno      = $_errno;
    $context->errstr     = $errstr;
    $context->errfile    = $errfile;
    $context->errline    = $errline;
    $context->errcontext = $errcontext;
    $context->stacktrace = debug_backtrace();

    if (is_object($errcontext)) {
      $context->errcontext = get_class($context->errcontext);
    }

    // Remove our handler from the stack if present:
    if ($context->stacktrace[0]["function"] == "errorHandler"
      && $context->stacktrace[0]["type"] == "::"
      && $context->stacktrace[0]["class"]
        == "BNETDocs\\Libraries\\GlobalErrorHandler") {
      array_shift($context->stacktrace);
    }

    // Gracefully back out of the user's request:
    self::gracefulExit($context);

    // Report this to the local web server's error log:
    // Ex: E_WARNING: something happened in file.php on line 123
    error_log($_errno . ": " . $errstr
      . " in " . $errfile . " on line " . $errline);

    // Report this to New Relic:
    if (extension_loaded("newrelic")) {
      newrelic_notice_error(
        $errno, $errstr, $errfile, $errline, $errcontext
      );
    }

    // Stop processing the rest of the application:
    exit();
  }

  public static function exceptionHandler(Exception $e) {
    // Back out of any output buffers:
    while (ob_get_level()) ob_end_clean();

    // Create a context object:
    $context             = new StdClass();
    $context->exception  = get_class($e);
    $context->code       = $e->getCode();
    $context->file       = $e->getFile();
    $context->line       = $e->getLine();
    $context->message    = $e->getMessage();
    $context->stacktrace = $e->getTrace();

    // Remove our handler from the stack if present:
    if ($context->stacktrace[0]["function"] == "exceptionHandler"
      && $context->stacktrace[0]["type"] == "::"
      && $context->stacktrace[0]["class"]
        == "BNETDocs\\Libraries\\GlobalErrorHandler") {
      array_shift($context->stacktrace);
    }

    // Gracefully back out of the user's request:
    self::gracefulExit($context);

    // Report this to the local web server's error log:
    // Ex: Exception #123: something happened in file.php on line 123
    error_log(
      $context->exception
      . ($context->code !== 0 ? " #" . $context->code : "") . ": "
      . $context->message
      . " in " . $context->file
      . " on line " . $context->line
    );
    error_log(var_export($context->stacktrace, true));

    // Report this to New Relic:
    if (extension_loaded("newrelic")) {
      newrelic_notice_error($e->getMessage(), $e);
    }

    // Stop processing the rest of the application:
    exit();
  }

  private static function gracefulExit(StdClass &$context) {
    // Return with a 500 Internal Server Error:
    http_response_code(500);

    // Tell the browser not to cache this response:
    header("Cache-Control: max-age=0,must-revalidate,no-cache,no-store");
    header("Expires: 0");
    header("Pragma: max-age=0");

    // Respond with some content about the problem (don't whitepage):
    if (!ini_get("display_errors")) {
      header("Content-Type: text/html;charset=utf-8");
      include(getenv("DOCUMENT_ROOT") . "/templates/GlobalErrorHandler.phtml");
    } else {
      if (PHP_VERSION >= 5.4) {
        header("Content-Type: application/json;charset=utf-8");
        echo json_encode($context, JSON_PRETTY_PRINT) . "\n";
      } else {
        header("Content-Type: text/plain;charset=utf-8");
        var_dump($context);
      }
    }
  }

}
