<?php

namespace BNETDocs\Libraries;

use \LogicException;

final class Router
{
  public const CLI_ACCEPT_MIMETYPE = 'text/plain;q=0.4,application/json;q=0.4,text/*;q=0.1,*/*;q=0.1';
  public const CLI_REQUEST_URI = '/status';
  public const METHOD_GET = 'GET';
  public const METHOD_POST = 'POST';
  public const METHOD_HEAD = 'HEAD';

  private function __construct()
  {
    throw new LogicException('This static class cannot be constructed');
  }

  private static ?array $args = null;

  public static array $route_not_found = [];
  public static array $routes = [];

  public static string $controller_namespace = '\\BNETDocs\\Controllers\\';
  public static string $global_namespace = '\\BNETDocs\\';
  public static string $view_namespace = '\\BNETDocs\\Views\\';

  /**
   * Invokes a Controller using available routes for the request.
   *
   * @return void
   * @throws LogicException if a route is invalid.
   */
  public static function invoke(): void
  {
    $uri = (\getenv('REQUEST_URI') ?? null);
    if (empty($uri) && \php_sapi_name() == 'cli') $uri = self::CLI_REQUEST_URI;
    if (\strpos($uri, '?') !== false) $uri = \explode('?', $uri)[0];

    foreach (self::$routes as $route)
    {
      if (\count($route) < 3)
        throw new LogicException(\sprintf('Invalid route, too few values: %s', $route[0]));

      $route_uri = \array_shift($route);
      $route_controller = \array_shift($route);
      $route_views = \array_shift($route);

      if (\preg_match($route_uri, $uri, $captured_args) !== 1) continue;
      \array_shift($captured_args); // discard the $route_uri ($0)

      // Found
      $route_args = \array_merge($route, $captured_args);
      $controller = (
        (\substr($route_controller, 0, \strlen(self::$controller_namespace)) !== self::$controller_namespace
          && \substr($route_controller, 0, \strlen(self::$global_namespace)) !== self::$global_namespace) ?
          self::$controller_namespace . $route_controller : $route_controller
      );
      self::invokeController(new $controller, self::negotiateView($route_views), $route_args);
      return;
    }

    // Not Found
    $route_controller = self::$route_not_found[0];
    $controller = (
      (\substr($route_controller, 0, \strlen(self::$controller_namespace)) !== self::$controller_namespace
        && \substr($route_controller, 0, \strlen(self::$global_namespace)) !== self::$global_namespace) ?
        self::$controller_namespace . $route_controller : $route_controller
    );
    self::invokeController(new $controller, self::negotiateView(self::$route_not_found[1]), null);
  }

  protected static function invokeController(\BNETDocs\Interfaces\Controller $controller, string $view, ?array $args): void
  {
    if (!$controller->invoke($args)) return;

    \ob_start();
    $view::invoke($controller->model);
    \http_response_code($controller->model->_responseCode ?? 500);
    foreach ($controller->model->_responseHeaders ?? [] as $key => $value)
      \header(\sprintf('%s: %s', $key, $value));
    \ob_end_flush();
  }

  /**
   * Identifies the best View class to serve for the route.
   * Using the "Accept" HTTP header, MIME-type weights are applied per RFC 7231 <https://httpwg.org/specs/rfc7231.html#header.accept>.
   * If no preferred MIME-type can be accepted, the negotiated view will be the first in the set from $route_views.
   *
   * @param array $route_views A set of View class names associated with this route.
   * @return string The negotiated ViewBase class or subclass, to be used by the Router to invoke the Controller.
   * @throws LogicException if a class is invalid.
   */
  protected static function negotiateView(array $route_views): string
  {
    // Accept header examples.
    // Firefox 98 on Windows 10: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8=

    // Collect available MIME-types.
    $default_view = null;
    $mimetypes = [];
    foreach ($route_views as $view_class)
    {
      $view_class = (
        (\substr($view_class, 0, \strlen(self::$view_namespace)) !== self::$view_namespace
          && \substr($view_class, 0, \strlen(self::$global_namespace)) !== self::$global_namespace) ?
          self::$view_namespace . $view_class : $view_class
      );
      if (!\class_exists($view_class)) throw new \BNETDocs\Exceptions\InvalidViewException($view_class);
      if (!$default_view) $default_view = $view_class;
      $view_class_mimetype = $view_class::mimeType();
      if (strpos($view_class_mimetype, ';') !== false) $view_class_mimetype = \explode(';', $view_class_mimetype)[0];
      $mimetypes[$view_class_mimetype] = $view_class;
    }

    // Separate groups of MIME-types from "Accept" header.
    $preferred_mimetype = $default_view::mimeType();
    if (strpos($preferred_mimetype, ';') !== false) $preferred_mimetype = \explode(';', $preferred_mimetype)[0];
    $accept_header = \getenv('HTTP_ACCEPT') ?? null;
    if ((empty($accept_header) || $accept_header == '*/*') && \php_sapi_name() == 'cli') $accept_header = self::CLI_ACCEPT_MIMETYPE;
    if (empty($accept_header)) $accept_header = '*/*';
    $accept_header = \strtolower(\str_replace(' ', '', $accept_header));

    $accept_mimetypes = null;
    if (\preg_match('/((?:[\w\+\-\*]+\/[\w\+\-\*]+)(?:;q=[0-9]+\.[0-9]+)?)/', $accept_header, $accept_mimetypes) !== 1)
    {
      // "Accept" header does not match expected pattern, cannot parse into groups
      return $default_view;
    }

    foreach ($mimetypes as $view_class_mimetype => $view_class)
    {
      foreach ($accept_mimetypes as $accept_mimetype)
      {
        if (strpos($accept_mimetype, ';') !== false) $accept_mimetype = \explode(';', $accept_mimetype)[0];
        if (\strtolower($accept_mimetype) == \strtolower($view_class_mimetype))
          return $view_class;

        list($accept_mimetype_0, $accept_mimetype_1) = \explode('/', $accept_mimetype);
        list($view_class_mimetype_0, $view_class_mimetype_1) = \explode('/', $view_class_mimetype);
        if (\strtolower($accept_mimetype_0) == \strtolower($view_class_mimetype_0)
          && ($accept_mimetype_1 == '*' || $view_class_mimetype_1 == '*'))
          return $view_class;
      }
    }

    return $mimetypes[$preferred_mimetype]; // cannot find preferrable mimetype, use default view
  }

  public static function requestMethod(): string
  {
    return \getenv('REQUEST_METHOD') ?? '';
  }

  public static function query(): array
  {
    if (!is_null(self::$args)) return self::$args;

    $url_args = [];
    $body_args = [];

    \parse_str(\getenv('QUERY_STRING'), $url_args);

    if (self::requestMethod() == self::METHOD_POST)
    {
      $body_str = \file_get_contents('php://input');
      $body_mimetype = \getenv('HTTP_CONTENT_TYPE') ?? '';
      if (\stripos($body_mimetype, 'application/json') !== false
        || \stripos($body_mimetype, 'text/json') !== false)
        $body_args = \json_decode($body_str, true);
      else
        \parse_str($body_str, $body_args);
    }

    self::$args = \array_merge($url_args, $body_args);
    return self::$args;
  }
}
