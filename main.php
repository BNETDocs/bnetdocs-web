<?php
  
  /**
   * main - defines the entrypoint for the project.
   */
  
  header('X-Frame-Options: DENY');
  header('X-Remote-Host: ' . $_SERVER['REMOTE_ADDR'] . ':' . $_SERVER['REMOTE_PORT']);
  error_reporting(E_ALL | E_STRICT);
  
  /**
   * this project uses code that is only available in PHP 5.4 (and possibly newer).
   */
  if (PHP_VERSION < 5.4) {
    trigger_error('PHP version installed on this server is older than 5.4.', E_USER_ERROR);
  }
  
  /**
   * configure an error handler that returns a HTTP 500 error.
   * provide an email link to notify staff about this issue.
   */
  set_error_handler(function($iErrorNumber, $sErrorMessage, $sErrorFile, $iErrorLine, $oErrorContext){
    // The following values may have been overridden before we got here:
    http_response_code(500);
    header('Cache-Control: max-age=0, must-revalidate, no-cache, no-store');
    header('Content-Type: text/html;charset=utf-8');
    
    global $_CONFIG;
    
    $aErrorHandling = (
      isset($_CONFIG)
      && is_array($_CONFIG)
      && array_key_exists('error_handling', $_CONFIG)
      ? $_CONFIG['error_handling']
      : array(
        'debug_mode' => false,
        'encryption_key' => 'bnetdocs+dev$!'
      )
    );
    $bDebugMode    = $aErrorHandling['debug_mode'];
    $sEncryptedKey = $aErrorHandling['encryption_key'];
    
    $sFullURL   = BNETDocs::fGetCurrentFullURL();
    $sMethod    = $_SERVER['REQUEST_METHOD'];
    $sTimestamp = date('F d Y H:i:s T');
    $sIPAddress = $_SERVER['REMOTE_ADDR'];
    
    $sUnencryptedData = json_encode(array(
      "collected_data"    => array(
        "ip_address"      => $sIPAddress,
        "method"          => $sMethod,
        "timestamp"       => $sTimestamp,
        "url"             => $sFullURL,
      ),
      "error_data" => array(
        "errno"           => $iErrorNumber,
        "errstr"          => $sErrorMessage,
        "errfile"         => $sErrorFile,
        "errline"         => $iErrorLine,
        "errcontext_meta" => array(
          "gettype"       => gettype($oErrorContext),
          "get_class"     => (is_object($oErrorContext) ? get_class($oErrorContext) : false),
        ),
      ),
    ), JSON_PRETTY_PRINT);
    if ($bDebugMode) {
      $sErrorData = $sUnencryptedData;
    } else {
      $sEncryptedData = wordwrap(base64_encode(mcrypt_encrypt(
        MCRYPT_RIJNDAEL_256,
        md5($sEncryptedKey),
        $sUnencryptedData,
        MCRYPT_MODE_CBC,
        md5(md5($sEncryptedKey))
      )), 80, "\n", true);
      /*
      $sDecryptedData = rtrim(mcrypt_decrypt(
        MCRYPT_RIJNDAEL_256,
        md5($sEncryptedKey),
        base64_decode($sEncryptedData),
        MCRYPT_MODE_CBC,
        md5(md5($sEncryptedKey))
      ), "\0");
      */
      $sErrorData = $sEncryptedData;
    }
    
    $sIssueTitle = 'Automatic Unhandled Error Report';
    $sIssueBody = "Hi,\n\nI just tried to access a page on BNETDocs, "
      . "but unfortunately when the page loaded, the server told me an internal "
      . "server error occurred.\n\nError Data:\n\n" . $sErrorData
      . "\n\nPlease investigate this issue so I can continue to use the website."
      . "\n\nThanks!\n";
    $sIssueURL = "mailto:" . Email::$oBNETDocsRecipient . "?subject="
      . rawurlencode($sIssueTitle) . "&amp;body=" . rawurlencode($sIssueBody);
    
    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "  <head>\n";
    echo "    <title>Server Error - BNETDocs</title>\n";
    echo "    <meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\" />\n";
    echo "    <style type=\"text/css\">\n";
    echo "      body { background: #fafafa; color: #000; font: 11pt sans-serif; margin: 0; padding: 0; text-align: center; }\n";
    echo "      div { background: #eaeaea; border-radius: 16px; box-sizing: border-box; margin: 32px auto; padding: 4px 10px; width: 600px; }\n";
    echo "      p.s { font-size: 8pt; }\n";
    echo "    </style>\n";
    echo "  </head>\n";
    echo "  <body>\n";
    echo "<!-- this error page isn't from nginx, so it couldn't have been too terrible. -->\n";
    echo "    <div>\n";
    echo "      <h1>500 Internal Server Error</h1>\n";
    echo "      <p>An internal server error occurred while processing your request. This could indicate a more serious problem, so please <a href=\"" . $sIssueURL . "\">report this to us</a>.</p>\n";
    echo "      <p class=\"s\">" . $sTimestamp . " &ndash; " . $sIPAddress . "</p>\n";
    echo "    </div>\n";
    echo "  </body>\n";
    echo "</html>\n";
    
    exit(1);
  }, E_ALL | E_STRICT);
  
  /**
   * the default response code is '200 OK', and probably no care as to caching.
   * we want to return '500 Internal Server Error' and disable caching until we prove otherwise.
   */
  http_response_code(500);
  header('Cache-Control: max-age=0, must-revalidate, no-cache, no-store');
  
  global $_CONFIG;
  try {
    $_CONFIG = file_get_contents('./config.json');
    $_CONFIG = json_decode($_CONFIG, true);
  } catch (Exception $oError) {
    throw new Exception(
      'The global config failed to be read or parsed correctly.',
      0,
      $oError
    );
  }
  
  if (!isset($_CONFIG) || !is_array($_CONFIG)
      || !array_key_exists('database', $_CONFIG)
      || !array_key_exists('engine', $_CONFIG['database'])
      || !array_key_exists('hostname', $_CONFIG['database'])
      || !array_key_exists('username', $_CONFIG['database'])
      || !array_key_exists('password', $_CONFIG['database'])
      || !array_key_exists('name', $_CONFIG['database'])
      || !array_key_exists('connect_timeout', $_CONFIG['database'])
      || !array_key_exists('character_set', $_CONFIG['database'])
      || !array_key_exists('audit', $_CONFIG['database'])
      || !is_string($_CONFIG['database']['engine'])
      || !is_string($_CONFIG['database']['hostname'])
      || !is_string($_CONFIG['database']['username'])
      || !is_string($_CONFIG['database']['password'])
      || !is_string($_CONFIG['database']['name'])
      || !is_numeric($_CONFIG['database']['connect_timeout'])
      || !is_string($_CONFIG['database']['character_set'])
      || !is_bool($_CONFIG['database']['audit'])
      || !array_key_exists('email_recipient', $_CONFIG)
      || !array_key_exists('address', $_CONFIG['email_recipient'])
      || !array_key_exists('name', $_CONFIG['email_recipient'])
      || !array_key_exists('prefers_plaintext', $_CONFIG['email_recipient'])
      || !is_string($_CONFIG['email_recipient']['address'])
      || !is_string($_CONFIG['email_recipient']['name'])
      || !is_bool($_CONFIG['email_recipient']['prefers_plaintext'])
      || !array_key_exists('error_handling', $_CONFIG)
      || !array_key_exists('debug_mode', $_CONFIG['error_handling'])
      || !array_key_exists('encryption_key', $_CONFIG['error_handling'])
      || !is_bool($_CONFIG['error_handling']['debug_mode'])
      || !is_string($_CONFIG['error_handling']['encryption_key'])
      || !array_key_exists('force_ssl', $_CONFIG)
      || !is_bool($_CONFIG['force_ssl'])
      || !array_key_exists('maintenance', $_CONFIG)
      || count($_CONFIG['maintenance']) != 2
      || !is_bool($_CONFIG['maintenance'][0])
      || !is_string($_CONFIG['maintenance'][1])
      || !array_key_exists('paths', $_CONFIG)
      || !array_key_exists('audit_dir', $_CONFIG['paths'])
      || !array_key_exists('base_dir', $_CONFIG['paths'])
      || !array_key_exists('core_dir', $_CONFIG['paths'])
      || !array_key_exists('models_dir', $_CONFIG['paths'])
      || !array_key_exists('static_dir', $_CONFIG['paths'])
      || substr($_CONFIG['paths']['audit_dir'], -1) != '/'
      || substr($_CONFIG['paths']['base_dir'], -1) != '/'
      || substr($_CONFIG['paths']['core_dir'], -1) != '/'
      || substr($_CONFIG['paths']['models_dir'], -1) != '/'
      || substr($_CONFIG['paths']['static_dir'], -1) != '/'
      || !array_key_exists('security', $_CONFIG)
      || !array_key_exists('disable_comments', $_CONFIG['security'])
      || !array_key_exists('disable_user_login', $_CONFIG['security'])
      || !array_key_exists('disable_user_registration', $_CONFIG['security'])
      || !array_key_exists('user_password_salt', $_CONFIG['security'])
      || !array_key_exists('session_encryption_key', $_CONFIG['security'])
      || !is_bool($_CONFIG['security']['disable_comments'])
      || !is_bool($_CONFIG['security']['disable_user_login'])
      || !is_bool($_CONFIG['security']['disable_user_registration'])
      || !is_string($_CONFIG['security']['user_password_salt'])
      || !is_string($_CONFIG['security']['session_encryption_key'])
      ) throw new Exception('The global config failed its verification check.', E_USER_ERROR);
  
  function __autoload($sClassName) {
    global $_CONFIG;
    require_once($_CONFIG['paths']['base_dir'] . $_CONFIG['paths']['core_dir'] . $sClassName . '.php');
  }
  
  if ($_CONFIG['maintenance'][0]) {
    http_response_code(503);
    header('Cache-Control: max-age=0, must-revalidate, no-cache, no-store');
    header('Content-Type: text/html;charset=utf-8');
?><!DOCTYPE html>
<html>
  <head>
    <title>Site Maintenance - BNETDocs</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <style type="text/css">
      body { background: #fafafa; color: #000; font: 11pt sans-serif; margin: 0; padding: 0; text-align: center; }
      div { background: #eaeaea; border-radius: 16px; box-sizing: border-box; margin: 32px auto; padding: 4px 10px; width: 600px; }
      p.s { font-size: 8pt; }
    </style>
  </head>
  <body>
    <div>
      <h1>Site Maintenance</h1>
      <p><?php echo $_CONFIG['maintenance'][1]; ?></p>
      <p class="s">Site Maintenance &ndash; <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
    </div>
  </body>
</html><?php
    exit(1);
  }
  
  if (BNETDocs::fInitialize()) {
    $oContext = new HTTPContext();
    $oContext->fSetRequestByServerGlobals();
    BNETDocs::fExecute($oContext);
    BNETDocs::fFinalize($oContext);
  }
  