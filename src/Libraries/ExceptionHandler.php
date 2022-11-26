<?php /* vim: set colorcolumn=: */

namespace BNETDocs\Libraries;

use \StdClass;

class ExceptionHandler
{
  private static $overridden_error_handler;
  private static $overridden_exception_handler;

  public static bool $continue_chain = false;

  private function __construct()
  {
    throw new \LogicException('This static class must not be constructed');
  }

  public static function errorHandler(
    int $errno = 0,
    string $errstr = '',
    string $errfile = '',
    int $errline = 0,
    $errcontext = null
  ) : bool
  {
    // Don't handle this error if it's turned off administratively:
    if (!(\error_reporting() & $errno)) return false;

    // Back out of any output buffers:
    while (\ob_get_level()) \ob_end_clean();

    // Determine error name from $errno:
    $_errno = self::phpErrorName($errno);

    // Create a context object:
    $context             = new StdClass();
    $context->errno      = $_errno;
    $context->errstr     = $errstr;
    $context->errfile    = $errfile;
    $context->errline    = $errline;
    $context->errcontext = $errcontext;
    $context->stacktrace = \debug_backtrace();

    if (\is_object($errcontext)) {
      $context->errcontext = \get_class($context->errcontext);
    }

    // Remove our handler from the stack if present:
    if ($context->stacktrace[0]['function'] == 'errorHandler'
      && $context->stacktrace[0]['type'] == '::'
      && $context->stacktrace[0]['class'] == 'BNETDocs\\Libraries\\ExceptionHandler')
    {
      \array_shift($context->stacktrace);
    }

    // Gracefully back out of the user's request:
    self::gracefulExit($context);

    // Report this to the local web server's error log:
    // Ex: E_WARNING: something happened in file.php on line 123
    \error_log(\sprintf(
      '%s: %s in %s on line %d',
      $_errno, $errstr, $errfile, $errline
    ));

    // Call the previous handler:
    if (self::$continue_chain && \is_callable(self::$overridden_error_handler))
    {
      \call_user_func_array(self::$overridden_error_handler, \func_get_args());
    }

    // Stop processing the rest of the application:
    exit();
  }

  public static function exceptionHandler(\Throwable $e) : void
  {
    // Back out of any output buffers:
    while (\ob_get_level()) \ob_end_clean();

    // Create a context object:
    $context             = new StdClass();
    $context->exception  = \get_class($e);
    $context->code       = $e->getCode();
    $context->file       = $e->getFile();
    $context->line       = $e->getLine();
    $context->message    = $e->getMessage();
    $context->stacktrace = $e->getTrace();

    // Remove our handler from the stack if present:
    if ($context->stacktrace[0]['function'] == 'exceptionHandler'
      && $context->stacktrace[0]['type'] == '::'
      && $context->stacktrace[0]['class'] == 'BNETDocs\\Libraries\\ExceptionHandler')
    {
      \array_shift($context->stacktrace);
    }

    // Gracefully back out of the user's request:
    self::gracefulExit($context);

    // Report this to the local web server's error log:
    // Ex: Exception #123: something happened in file.php on line 123
    \error_log(\sprintf(
      '%s: %s in %s on line %d',
      $context->exception . ($context->code !== 0 ? ' #' . $context->code : ''),
      $context->message, $context->file, $context->line
    ));
    \error_log(\var_export($context->stacktrace, true));

    // Call the previous handler:
    if (self::$continue_chain && \is_callable(self::$overridden_exception_handler))
    {
      \call_user_func_array(self::$overridden_exception_handler, \func_get_args());
    }

    // Stop processing the rest of the application:
    exit();
  }

  private static function gracefulExit(StdClass &$context) : void
  {
    // Return with a 500 Internal Server Error:
    if (\function_exists('http_response_code'))
    {
      http_response_code(500);
    }
    else
    {
      \header(\getenv('SERVER_PROTOCOL') . ' 500 Internal Server Error', true, 500);
    }

    // Tell the browser not to cache this response:
    \header('Cache-Control: max-age=0,must-revalidate,no-cache,no-store');
    \header('Expires: 0');
    \header('Pragma: max-age=0');

    // Respond with some content about the problem (don't whitepage):
    $display_errors = \ini_get('display_errors');
    if (!$display_errors || \strtolower($display_errors) == 'off')
    {
      (new \BNETDocs\Libraries\Template(null, 'ExceptionHandler'))->invoke();
    }
    else
    {
      \header('Content-Type: application/json;charset=utf-8');
      echo \json_encode($context, \JSON_PRETTY_PRINT) . \PHP_EOL;
    }
  }

  public static function phpErrorName(int $errno) : string
  {
    switch ($errno)
    {
      case \E_ERROR:             return 'E_ERROR';             /* 1     */
      case \E_WARNING:           return 'E_WARNING';           /* 2     */
      case \E_PARSE:             return 'E_PARSE';             /* 4     */
      case \E_NOTICE:            return 'E_NOTICE';            /* 8     */
      case \E_CORE_ERROR:        return 'E_CORE_ERROR';        /* 16    */
      case \E_CORE_WARNING:      return 'E_CORE_WARNING';      /* 32    */
      case \E_COMPILE_ERROR:     return 'E_COMPILE_ERROR';     /* 64    */
      case \E_COMPILE_WARNING:   return 'E_COMPILE_WARNING';   /* 128   */
      case \E_USER_ERROR:        return 'E_USER_ERROR';        /* 256   */
      case \E_USER_WARNING:      return 'E_USER_WARNING';      /* 512   */
      case \E_USER_NOTICE:       return 'E_USER_NOTICE';       /* 1024  */
      case \E_STRICT:            return 'E_STRICT';            /* 2048  */
      case \E_RECOVERABLE_ERROR: return 'E_RECOVERABLE_ERROR'; /* 4096  */
      case \E_DEPRECATED:        return 'E_DEPRECATED';        /* 8192  */
      case \E_USER_DEPRECATED:   return 'E_USER_DEPRECATED';   /* 16384 */
      case \E_ALL:               return 'E_ALL';               /* 32767 */
      default:                   return 'E_UNKNOWN';           /* ????? */
    }
  }

  public static function register() : void
  {
    self::$overridden_error_handler = \set_error_handler(
      '\\BNETDocs\\Libraries\\ExceptionHandler::errorHandler'
    );
    self::$overridden_exception_handler = \set_exception_handler(
      '\\BNETDocs\\Libraries\\ExceptionHandler::exceptionHandler'
    );
  }
}
