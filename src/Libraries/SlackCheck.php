<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Logger;
use \CarlBennett\MVC\Libraries\Common;

/**
 * Provides a set of methods to identify clients originating from Slack.
 */
class SlackCheck
{
  private const STATUS_NOT_CHECKED = 0;
  private const STATUS_NOT_SLACK = 1;
  private const STATUS_SLACK = 2;

  private static int $status = self::STATUS_NOT_CHECKED;

  /**
   * Block instantiation of this object. All public methods are static.
   */
  private function __construct() {}

  private static function check_for_slack()
  {
    self::$status = self::check_headers();
  }

  private static function check_headers()
  {
    $slack_fingerprint = [
      'ACCEPT' => '*/*',
      'USER_AGENT' => 'Slackbot-LinkExpanding 1.0 (+https://api.slack.com/robots)',
      'RANGE' => 'bytes=0-32768',
    ];

    $ignored_fingerprint = [
      'ACCEPT_ENCODING',
      'CDN_LOOP',
      'CF_CONNECTING_IP',
      'CF_IPCOUNTRY',
      'CF_RAY',
      'CF_VISITOR',
      'HOST',
      'X_FORWARDED_FOR',
      'X_FORWARDED_PROTO',
      'X_REAL_IP',
    ];

    $request_fingerprint = self::get_header_list();

    foreach ($ignored_fingerprint as $name)
    {
      if (isset($request_fingerprint[$name]))
      {
        unset($request_fingerprint[$name]);
      }
    }

    if (count($request_fingerprint) !== count($slack_fingerprint))
    {
      return self::STATUS_NOT_SLACK;
    }

    // verify the request's fingerprints against slack's fingerprints
    foreach ($request_fingerprint as $name => $value)
    {
      if (!isset($slack_fingerprint[$name]))
      {
        return self::STATUS_NOT_SLACK;
      }

      if ($slack_fingerprint[$name] !== $value)
      {
        return self::STATUS_NOT_SLACK;
      }
    }

    // cross-verify slack fingerprints against the request's fingerprints
    foreach ($slack_fingerprint as $name => $value)
    {
      if (!isset($request_fingerprint[$name]))
      {
        return self::STATUS_NOT_SLACK;
      }

      if ($request_fingerprint[$name] !== $value)
      {
        return self::STATUS_NOT_SLACK;
      }
    }

    // Slack was verified (fingerprints are identical, no other headers found, values match)
    return self::STATUS_SLACK;
  }

  private static function get_header_list()
  {
    $headers = [];
    foreach ($_SERVER as $name => $value)
    {
      if (preg_match('/^HTTP_/', $name))
      {
        $headers[substr($name, 5)] = $value;
      }
    }
    return $headers;
  }

  public static function is_slack()
  {
    if (self::$status === self::STATUS_NOT_CHECKED)
    {
      self::check_for_slack();
    }

    return (self::$status === self::STATUS_SLACK);
  }

  public static function log_slack_request()
  {
    if (!self::is_slack()) return; // do not log non-Slack requests

    $user_id = (isset(Authentication::$user) ? Authentication::$user->getId() : null);

    Logger::logEvent(
      EventTypes::SLACK_UNFURL,
      $user_id,
      getenv('REMOTE_ADDR'),
      json_encode([
        'method'     => getenv('REQUEST_METHOD'),
        'referer'    => getenv('HTTP_REFERER'),
        'uri'        => Common::relativeUrlToAbsolute(getenv('REQUEST_URI')),
        'user_agent' => getenv('HTTP_USER_AGENT'),
        'version'    => VersionInfo::$version,
      ])
    );
  }
}
