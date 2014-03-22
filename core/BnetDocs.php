<?php
  
  final class BnetDocs {
    
    public static $oDB;
    
    private function __construct() {} // We don't want to create objects of this class.
    
    public static function fExecute(HTTPContext $oContext) {
      
    }
    
    public static function fFinalize() {
      
    }
    
    public static function fGetCurrentFullURL($sForceURI = '') {
      
      if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
        $sShareURL = 'https://';
      } else {
        $sShareURL = 'http://';
      }
      
      if (\is_string($sForceURI) && !empty($sForceURI)
       && \substr($sForceURI, 0, 2) == '//') {
        
        $sShareURL .= \substr($sForceURI, 2);
      
      } else {
        
        if (isset($_SERVER['HTTP_HOST'])) {
          $sShareURL .= $_SERVER['HTTP_HOST'];
        } else if (isset($_SERVER['SERVER_NAME'])) {
          $sShareURL .= $_SERVER['SERVER_NAME'];
        } else {
          $sShareURL .= 'bnetdocs.org';
        }
        
        if (\is_string($sForceURI) && !empty($sForceURI)
         && \substr($sForceURI, 0, 1) == '/') {
          
          $sShareURL .= $sForceURI;
          
        } else {
          
          if (isset($_SERVER['REQUEST_URI'])) {
            $sShareURL .= \parse_url($_SERVER['REQUEST_URI'], \PHP_URL_PATH);
          } else {
            $sShareURL .= '/';
          }
          
          if (\is_string($sForceURI) && !empty($sForceURI)
           && \substr($sForceURI, 0, 1) == '?') {
            
            $sQuery = \substr($sForceURI, 1);
            
          } else {
            
            $sQuery = \http_build_query($_GET);
            
          }
          
          if (!empty($sQuery)) {
            $sShareURL .= '?' . $sQuery;
          }
          
        }
        
      }
      
      return $sShareURL;
      
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
  