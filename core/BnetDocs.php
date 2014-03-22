<?php
  
  final class BnetDocs {
    
    public static $oDB;
    
    private function __construct() {} // We don't want to create objects of this class.
    
    public static function fExecute(HTTPContext $oContext) {
      
    }
    
    public static function fFinalize() {
      
    }
    
    public static function fInitialize() {
      global $_CONFIG;
      
      /* Initialize Database Connection */
      
      self::$oDB = new $_CONFIG['database']['engine'];
      if (!is_subclass_of(self::$oDB, 'SQL'))
        throw new Exception('Selected database engine does not inherit class SQL.');
      
      if (!self::$oDB->fConnect()) {
        http_response_code(503);
        header('Content-Type: text/html;charset=utf-8');
        echo "<h1>503 Service Temporarily Unavailable</h1>\n";
        echo "<p>A connection could not be made to the database server.</p>\n";
        return false;
      }
      
      /* Other Stuff? */
      
      return true;
    }
    
  }
  