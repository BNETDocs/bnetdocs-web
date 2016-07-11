<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\IP;
use \DateInterval;
use \DateTime;
use \DateTimeZone;
use \StdClass;

final class Common {

  public static $cache;
  public static $config;
  public static $database;
  public static $version;

  /**
   * Block instantiation of this object.
   */
  private function __contruct() {}

  public static function checkIfBlizzard() {
    $IP    = getenv("REMOTE_ADDR");
    $CIDRs = file_get_contents(getcwd() . "/static/a/Blizzard-CIDRs.txt");
    $CIDRs = preg_replace("/^#.*?\n/sm", "", $CIDRs);
    $CIDRs = self::stripLinesWith($CIDRs, "\n");
    $CIDRs = explode("\n", $CIDRs);
    return IP::checkCIDRArray($IP, $CIDRs);
  }

  public static function curlRequest($url, $post_content = null,
      $content_type = "", $connect_timeout = 5, $max_redirects = 10) {
    $curl = curl_init();
    $time = microtime(true);

    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $connect_timeout);

    curl_setopt($curl, CURLOPT_AUTOREFERER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_MAXREDIRS, $max_redirects);
    curl_setopt($curl, CURLOPT_POSTREDIR, 7);

    curl_setopt($curl, CURLOPT_URL, $url);

    if ($post_content) {
      curl_setopt($curl, CURLOPT_POST, true);
      if (PHP_VERSION >= 5.5) {
        // disable processing of @ symbol as a filename in CURLOPT_POSTFIELDS.
        curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
      }
      curl_setopt($curl, CURLOPT_POSTFIELDS, $post_content);
      if (!empty($content_type)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
          "Content-Type: " . $content_type
        ]);
      }
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response       = new StdClass();
    $response->data = curl_exec($curl);
    $response->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $response->type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
    $response->time = microtime(true) - $time;

    curl_close($curl);
    return $response;
  }

  public static function formatFileSize($value) {

    $bytes     = $value;
    $kilobytes = 1024;
    $megabytes = 1024 * $kilobytes;
    $gigabytes = 1024 * $megabytes;
    $terabytes = 1024 * $gigabytes;

    if ($bytes >= $terabytes) {
      $bytes = round($bytes / $terabytes, 2) . " TiB";
    } else if ($bytes >= $gigabytes) {
      $bytes = round($bytes / $gigabytes, 2) . " GiB";
    } else if ($bytes >= $megabytes) {
      $bytes = round($bytes / $megabytes, 2) . " MiB";
    } else if ($bytes >= $kilobytes) {
      $bytes = round($bytes / $kilobytes, 2) . " KiB";
    } else {
      $bytes = $bytes . " B";
    }

    return $bytes;
  }

  public static function getVersionProperties() {
    $versions           = new StdClass();
    $versions->bnetdocs = file_get_contents("../etc/.rsync-version");
    $versions->newrelic = phpversion("newrelic");
    $versions->php      = phpversion();
    return $versions;
  }

  public static function intervalToString($di, $zero_interval = "") {
    if (!$di instanceof DateInterval) return null;
    $buf = "";
    if ($di->y) { if ($buf) $buf .= ", "; $buf .= $di->y . " year";   if ($di->y != 1) $buf .= "s"; }
    if ($di->m) { if ($buf) $buf .= ", "; $buf .= $di->m . " month";  if ($di->m != 1) $buf .= "s"; }
    if ($di->d) { if ($buf) $buf .= ", "; $buf .= $di->d . " day";    if ($di->d != 1) $buf .= "s"; }
    if ($di->h) { if ($buf) $buf .= ", "; $buf .= $di->h . " hour";   if ($di->h != 1) $buf .= "s"; }
    if ($di->i) { if ($buf) $buf .= ", "; $buf .= $di->i . " minute"; if ($di->i != 1) $buf .= "s"; }
    if ($di->s) { if ($buf) $buf .= ", "; $buf .= $di->s . " second"; if ($di->s != 1) $buf .= "s"; }
    if (!$buf) $buf = $zero_interval;
    // Splice the "and" keyword and take care of commas if necessary. We support the Oxford comma!
    if (strpos($buf, ", ") !== false) {
      $buf = explode(", ", $buf); $i = count($buf) - 1;
      $buf[$i] = "and " . $buf[$i];
      if ($i == 1) $buf = implode(" ", $buf); else $buf = implode(", ", $buf);
    }
    return $buf;
  }

  public static function isBrowser($user_agent) {
    // Browser names
    if (stripos($user_agent, "Mozilla" ) !== false) return true;
    if (stripos($user_agent, "Firefox" ) !== false) return true;
    if (stripos($user_agent, "Chrome"  ) !== false) return true;
    if (stripos($user_agent, "Chromium") !== false) return true;
    if (stripos($user_agent, "Safari"  ) !== false) return true;
    if (stripos($user_agent, "OPR"     ) !== false) return true;
    if (stripos($user_agent, "Opera"   ) !== false) return true;
    if (stripos($user_agent, "MSIE"    ) !== false) return true;

    // Rendering engine names
    if (stripos($user_agent, "Gecko"   ) !== false) return true;
    if (stripos($user_agent, "WebKit"  ) !== false) return true;
    if (stripos($user_agent, "Presto"  ) !== false) return true;
    if (stripos($user_agent, "Trident" ) !== false) return true;
    if (stripos($user_agent, "Blink"   ) !== false) return true;

    // Not a browser
    return false;
  }

  public static function phpErrorName($errno) {
    switch ($errno) {
      case E_ERROR:             return "E_ERROR";             /* 1     */
      case E_WARNING:           return "E_WARNING";           /* 2     */
      case E_PARSE:             return "E_PARSE";             /* 4     */
      case E_NOTICE:            return "E_NOTICE";            /* 8     */
      case E_CORE_ERROR:        return "E_CORE_ERROR";        /* 16    */
      case E_CORE_WARNING:      return "E_CORE_WARNING";      /* 32    */
      case E_COMPILE_ERROR:     return "E_COMPILE_ERROR";     /* 64    */
      case E_COMPILE_WARNING:   return "E_COMPILE_WARNING";   /* 128   */
      case E_USER_ERROR:        return "E_USER_ERROR";        /* 256   */
      case E_USER_WARNING:      return "E_USER_WARNING";      /* 512   */
      case E_USER_NOTICE:       return "E_USER_NOTICE";       /* 1024  */
      case E_STRICT:            return "E_STRICT";            /* 2048  */
      case E_RECOVERABLE_ERROR: return "E_RECOVERABLE_ERROR"; /* 4096  */
      case E_DEPRECATED:        return "E_DEPRECATED";        /* 8192  */
      case E_USER_DEPRECATED:   return "E_USER_DEPRECATED";   /* 16384 */
      case E_ALL:               return "E_ALL";               /* 32767 */
      default:                  return "E_UNKNOWN";           /* ????? */
    }
  }

  public static function prettyJSONIfBrowser($flags = 0) {
    if (self::isBrowser(getenv("HTTP_USER_AGENT")))
      $flags |= JSON_PRETTY_PRINT;
    return $flags;
  }

  public static function relativeDateTimeString(DateTime $compare) {
    $diff = (new DateTime("now", new DateTimeZone("UTC")))->diff($compare);
    $string = self::intervalToString($diff);
    $string = ($string == "" ? "just now" : $string . " ago");
    return $string;
  }

  public static function relativeUrlToAbsolute($value) {
    // Current request
    $current_scheme = "https:";
    $current_host   = getenv("HTTP_HOST");
    $current_path   = getenv("DOCUMENT_URI");
    $current_query  = getenv("QUERY_STRING");

    // Placeholders
    $scheme = null;
    $host   = null;
    $path   = null;
    $query  = null;

    // Split off query part
    $i = strpos($value, "?");
    if ($i !== false) {
      $query = substr($value, $i + 1);
      $value = substr($value, 0, $i);
    }

    // Retrieve the scheme from the $value
    $i = strpos($value, "//");
    if ($i !== false) {
      $scheme = substr($value, 0, $i);
      $value  = substr($value, $i);
    }
    if (empty($scheme)) $scheme = null; // Use current scheme further down

    // Retrieve the host from the $value
    if (substr($value, 0, 2) == "//") {
      $value = substr($value, 2);
      $i     = strpos($value, "/");
      if ($i === false) {
        $host  = $value;
        $value = "";
      } else {
        $host  = substr($value, 0, $i);
        $value = substr($value, $i);
      }
    }

    // All what's left is the path
    $path  = $value;
    $value = "";

    // If the path is empty, substitute our own
    if (empty($path)) $path = $current_path;

    // If the path is relative, splice it into current path
    if (substr($path, 0, 1) != "/") {
      $dir = dirname($current_path);
      if ($dir == ".") {
        $path = "/" . $path;
      } else {
        $path = "/" . $dir . "/" . $path;
      }
    }

    // Use current values if none provided
    if (is_null($scheme)) $scheme = $current_scheme;
    if (is_null($host  )) $host   = $current_host;
    if (is_null($path  )) $path   = $current_path;

    // Build the url
    return $scheme . "//" . $host . $path . ($query ? "?" . $query : "");
  }

  public static function sanitizeForUrl($haystack, $lowercase = true) {
    $result = trim(preg_replace("/[^\da-z]+/im", "-", $haystack), "-");
    if ($lowercase) $result = strtolower($result);
    return $result;
  }

  public static function stripExcessLines($buffer) {
    return preg_replace("/\n\n+/", "\n\n", $buffer);
  }

  public static function stripLeftPattern($haystack, $needle) {
    $needle_l = strlen($needle);
    if (substr($haystack, 0, $needle_l) == $needle) {
      return substr($haystack, $needle_l);
    } else {
      return $haystack;
    }
  }

  public static function stripLinesWith($buffer, $pattern) {
    return preg_replace("/\s+/", $pattern, $buffer);
  }

  public static function stripToSnippet($buffer, $length) {
    $buflen = strlen($buffer);
    if ($buflen <= $length) return $buffer;
    return preg_replace(
      "/\s+?(\S+)?$/",
      "",
      substr($buffer, 0, $length - 2)
    ) . "...";
  }

  public static function stripUpTo($buffer, $chr, $len = 0) {
    $i = strpos($buffer, $chr);
    if ($i === false && $len <= 0) {
      return $buffer;
    } else if ($i === false && $len > 0) {
      return self::stripToSnippet($buffer, $len);
    } else {
      return substr($buffer, 0, $i);
    }
  }

}
