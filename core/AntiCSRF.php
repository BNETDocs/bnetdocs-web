<?php
  
  class AntiCSRF {
    
    /**
     * Length that the token will be good for in seconds.
     **/
    const TOKEN_LENGTH = 120;
    
    public static function fCheckToken($sTheirToken) {
      $sOurToken = self::fGetToken();
      return (strtoupper($sTheirToken) == strtoupper($sOurToken));
    }
    
    private static function fGetTimeSlot() {
      return floor(time() / self::TOKEN_LENGTH);
    }
    
    public static function fGetToken() {
      global $_CONFIG;
      return hash('sha256', $_CONFIG['security']['csrf_salt'] . self::fGetTimeSlot());
    }
    
  }
  