<?php
  
  final class BNETDocs {
    
    public static $oDB;
    public static $oUser;
    
    private function __construct() {} // We don't want to create objects of this class.
    
    public static function fCurlRequest($sURL, $sPostContentData = null) {
      
      if (!\is_string($sURL))
        throw new \Exception('URL is not of type string');
      if (\strlen($sURL) == 0)
        throw new \Exception('URL cannot be an empty string');
      if (!\is_null($sPostContentData) && !\is_array($sPostContentData))
        throw new \Exception('PostContentData is not of type null or array');
      
      $oCurl = \curl_init();
      
      \curl_setopt($oCurl, \CURLOPT_CONNECTTIMEOUT, 5);
      
      \curl_setopt($oCurl, \CURLOPT_FOLLOWLOCATION, true);
      \curl_setopt($oCurl, \CURLOPT_MAXREDIRS, 10);
      
      \curl_setopt($oCurl, \CURLOPT_URL, $sURL);
      
      if (!\is_null($sPostContentData) && \is_array($sPostContentData)) {
        \curl_setopt($oCurl, \CURLOPT_POST, true);
        \curl_setopt($oCurl, \CURLOPT_POSTFIELDS, \http_build_query($sPostContentData));
      }
      
      \curl_setopt($oCurl, \CURLOPT_RETURNTRANSFER, true);
      
      $sResponseData = \curl_exec($oCurl);
      $sResponseType = \curl_getinfo($oCurl, \CURLINFO_CONTENT_TYPE);
      
      \curl_close($oCurl);
      
      return array($sResponseData, $sResponseType);
      
    }
    
    public static function fExecute(HTTPContext &$oContext) {
      
      $oContext->fSetResponseCode(404);
      $oContext->fSetResponseContent('Page not found.');
      $oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
      $oContext->fSetResponseHeader('Content-Type', 'text/plain;charset=utf-8');
      
      global $_CONFIG;
            
      $sRootPath = $_CONFIG['paths']['base_dir'] . $_CONFIG['paths']['template_dir'];
      
      $sPrevCwd = getcwd();
      if (!chdir($sRootPath))
        throw new Exception('Cannot change directory to the template directory');
      
      $sRelPath = $oContext->fGetRequestPath();
      if (substr($sRelPath, 0, 1) != '/') {
        $oContext->fSetResponseCode(400);
        $oContext->fSetResponseContent('Bad request.');
        return;
      }
      
      if (substr($sRelPath, -1) == '/')
        $sRelPath .= 'index';
      
      // TODO: Handle virtual files, like /news/123 goes to ./pages/news.php if ./pages/news/123.php doesn't exist
      
      $sFullPath = './pages/' . $sRelPath . '.php';
      
      // TODO: Advanced confirmation that their path is inside our root path.
      
      if (file_exists($sFullPath) && is_file($sFullPath)) {
        include($sFullPath);
      }
      
      chdir($sPrevCwd);
      
    }
    
    public static function fExpandIPv6($sIPAddress) {
      
      $aHex = \unpack("H*hex", \inet_pton($sIPAddress)); 
      
      return \substr(
        \preg_replace("/([A-f0-9]{4})/", "$1:", $aHex['hex']), 0, -1
      );
      
    }
    
    public static function fFinalize(HTTPContext &$oContext) {
      
      http_response_code($oContext->fGetResponseCode());
      
      $aHeaders = $oContext->fGetResponseHeaders();
      foreach ($aHeaders as $sHeaderName => $sHeaderValue) {
        header($sHeaderName . ': ' . $sHeaderValue, true);
      }
      
      $aCookies = $oContext->fGetResponseCookies();
      foreach ($aCookies as $oCookie) {
        setcookie(
          $oCookie->fGetName(),
          $oCookie->fGetValue(),
          $oCookie->fGetExpire(),
          $oCookie->fGetPath(),
          $oCookie->fGetDomain(),
          $oCookie->fGetSecure(),
          $oCookie->fGetHTTPOnly()
        );
      }
      
      $mContent = $oContext->fGetResponseContent();
      if (is_resource($mContent) && get_resource_type($mContent) == 'stream') {
        
        // Response content is within a stream object:
        while (!feof($mContent)) {
          echo fread($mContent, 1048576); // 1048576 B == 1 MiB
        }
        fclose($mContent);
        
      } else if (is_array($mContent)) {
        
        // Response content is within an array.
        throw new Exception('Content is of type array, and should have been translated earlier');
        
      } else {
        
        // Hopefully the response is displayable through echo:
        
        /*if (function_exists('\gzencode')
         && stripos(
           $oContext->fGetRequestHeader('ACCEPT_ENCODING', ''), 'gzip'
         ) !== false) {
          $sCompressedContent = gzencode($mContent);
          $iCompressedContent = strlen($sCompressedContent);
          $iContent           = strlen($mContent);
          header('Content-Encoding: gzip');
          header('Content-Length: ' . strlen($sCompressedContent));
          header('X-Compression-Rate: ' .
            sprintf("%d", 100 - ($iCompressedContent / $iContent * 100)) . '%');
          echo $sCompressedContent;
        } else {*/
          echo $mContent;
        /*}*/
        
      }
      
    }
    
    public static function fGenerateUUIDv4() {
      // source: http://us.php.net/manual/en/function.uniqid.php#94959
      return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        
        // 32 bits for "time_low"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        
        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),
        
        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,
        
        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,
        
        // 48 bits for "node"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        
      );
    }
    
    public static function fGetCurrentFullURL($sForceURI = '', $bFilterAmpersand = false) {
      
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
      
      if ($bFilterAmpersand)
        $sShareURL = \str_replace('&', '&amp;', $sShareURL);
      
      return $sShareURL;
      
    }
    
    public static function fGetServerPort() {
      
      return (isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80);
      
    }
    
    public static function fInitialize() {
      global $_CONFIG;
      
      /* Initialize Database Connection */
      
      self::$oDB = new $_CONFIG['database']['engine'];
      if (!is_subclass_of(self::$oDB, 'SQL'))
        throw new Exception('Selected database engine does not inherit class SQL.');
      
      if (!self::$oDB->fConnect())
        throw new Exception('Unable to connect to the database server');
      
      /* Global Email Recipient */
      
      Email::$oBNETDocsRecipient = new EmailRecipient(
        'no-reply@bnetdocs.org',    // Email address
        EmailRecipient::TYPE_FROM,  // Designation of address
        'BNETDocs',                 // Display name
        false                       // Prefers plaintext
      );
      
      /* Other Stuff? */
      
      return true;
    }
    
    public static function fNormalizeIP($sIPAddress) {
      
      return \strtoupper(\bin2hex(\inet_pton($sIPAddress)));
      
    }
    
    public static function fTranslateArrayByKeyStart(
      array &$aOldArray,
      $sSubstring,
      $bTrimSubstring
    ) {
      
      if (!\is_string($sSubstring))
        throw new \Exception('Substring is not of type string');
      if (\strlen($sSubstring) == 0)
        throw new \Exception('Substring cannot be an empty string');
      if (!\is_bool($bTrimSubstring))
        throw new \Exception('TrimSubstring is not of type bool');
      
      $aNewArray  = array();
      $iSubstring = \strlen($sSubstring);
      
      if (!$bTrimSubstring) {
        foreach ($aOldArray as $sKey => $mVal) {
          if (\substr($sKey, 0, $iSubstring) == $sSubstring) {
            $aNewArray[$sKey] = $mVal;
          }
        }
      } else {
        foreach ($aOldArray as $sKey => $mVal) {
          if (\substr($sKey, 0, $iSubstring) == $sSubstring) {
            $aNewArray[\substr($sKey, $iSubstring)] = $mVal;
          }
        }
      }
      
      return $aNewArray;
      
    }
    
  }
  