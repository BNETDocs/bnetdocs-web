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
      || !array_key_exists($_CONFIG, 'database')
      || !array_key_exists($_CONFIG['database'], 'engine')
      || !array_key_exists($_CONFIG['database'], 'hostname')
      || !array_key_exists($_CONFIG['database'], 'username')
      || !array_key_exists($_CONFIG['database'], 'password')
      || !array_key_exists($_CONFIG['database'], 'name')
      || !array_key_exists($_CONFIG['database'], 'connect_timeout')
      || !array_key_exists($_CONFIG['database'], 'character_set')
      || !array_key_exists($_CONFIG, 'maintenance')
      || count($_CONFIG['maintenance']) != 2
      || !is_bool($_CONFIG['maintenance'][0])
      || !is_string($_CONFIG['maintenance'][1])
      || !array_key_exists($_CONFIG, 'paths')
      || !array_key_exists($_CONFIG['paths'], 'base_dir')
      || !array_key_exists($_CONFIG['paths'], 'core_dir')
      || !array_key_exists($_CONFIG['paths'], 'static_dir')
      || !array_key_exists($_CONFIG['paths'], 'template_dir')
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
  