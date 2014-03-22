<?php
  
  /**
   * main - defines the entrypoint for the project.
   */
  
  header('X-Frame-Options: DENY');
  header('X-Remote-Host: ' . $_SERVER['REMOTE_ADDR'] . ':' . $_SERVER['REMOTE_PORT']);
  
  /**
   * this project uses code that is only available in PHP 5.4 (and possibly newer).
   * we give a warning instead of an error because the specific PHP 5.4 changes might not get ran.
   */
  if (PHP_VERSION < 5.4) {
    trigger_error('PHP version installed on this server is older than 5.4.', E_USER_WARNING);
  }
  
  /**
   * the default response code is '200 OK', and probably no care as to caching.
   * we want to return '500 Internal Server Error' and disable caching until we prove otherwise.
   */
  http_response_code(500);
  header('Cache-Control: max-age=0, must-revalidate, no-cache, no-store');
  
  global $_CONFIG;
  try {
    $_CONFIG = file_get_contents('config.json');
    $_CONFIG = json_decode($_CONFIG, true);
  } catch (Exception $error) {
    trigger_error('An unhandled ' . get_class($error) . ' error occurred while trying to read the global config.', E_USER_ERROR);
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
      || !array_key_exists('maintenance', $_CONFIG)
      || count($_CONFIG['maintenance']) != 2
      || !is_bool($_CONFIG['maintenance'][0])
      || !is_string($_CONFIG['maintenance'][1])
      || !array_key_exists('paths', $_CONFIG)
      || !array_key_exists('base_dir', $_CONFIG['paths'])
      || !array_key_exists('core_dir', $_CONFIG['paths'])
      || !array_key_exists('static_dir', $_CONFIG['paths'])
      || !array_key_exists('template_dir', $_CONFIG['paths'])
      || substr($_CONFIG['paths']['base_dir'], -1) != '/'
      || substr($_CONFIG['paths']['core_dir'], -1) != '/'
      || substr($_CONFIG['paths']['static_dir'], -1) != '/'
      || substr($_CONFIG['paths']['template_dir'], -1) != '/'
      ) trigger_error('The global config failed its verification check.', E_USER_ERROR);
  
  function __autoload($sClassName) {
    global $_CONFIG;
    require_once($_CONFIG['paths']['core_dir'] . $sClassName . '.php');
  }
  
  if (BnetDocs::fInitialize()) {
    BnetDocs::fExecute(new HTTPContext());
    BnetDocs::fFinalize();
  }
  