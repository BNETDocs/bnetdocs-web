<?php
  
  class UserSession {
    
    const SESSION_COOKIE_NAME = 'session';
    
    private $oContext;
    private $oUser;
    
    public function __construct() {
      $aArgs = func_get_args();
      $iArgs = count($aArgs);
      if ($iArgs == 1 && $aArgs[0] instanceof User) {
        $this->oUser = $aArgs[0];
      } else if ($iArgs == 1 && $aArgs[0] instanceof HTTPContext) {
        $this->oContext = $aArgs[0];
      } else if ($iArgs == 2 && $aArgs[0] instanceof User && $aArgs[1] instanceof HTTPContext) {
        $this->oUser = $aArgs[0];
        $this->oContext = $aArgs[1];
      } else if ($iArgs == 2 && $aArgs[0] instanceof HTTPContext && $aArgs[1] instanceof User) {
        $this->oContext = $aArgs[0];
        $this->oUser = $aArgs[1];
      } else {
        throw new Exception('Wrong number of arguments or wrong argument types');
      }
      if (isset($this->oContext) && !isset($this->oUser)) {
        $this->fSetUserObjectByCookies();
      }
    }
    
    public static function fEncryptCookieValue($sValue) {
      if (!is_string($sValue))
        throw new RecoverableException('Value is not of type string');
      global $_CONFIG;
      $sKey = $_CONFIG['security']['session_encryption_key'];
      if (empty($sKey)) $sKey = "bnetdocs+session~@$";
      return base64_encode(mcrypt_encrypt(
        MCRYPT_RIJNDAEL_256,
        md5($sKey),
        $sValue,
        MCRYPT_MODE_CBC,
        md5(md5($sKey))
      ));
    }
    
    public static function fDecryptCookieValue($sValue) {
      if (!is_string($sValue))
        throw new RecoverableException('Value is not of type string');
      global $_CONFIG;
      $sKey = $_CONFIG['security']['session_encryption_key'];
      if (empty($sKey)) $sKey = "bnetdocs+session~@$";
      return rtrim(mcrypt_decrypt(
        MCRYPT_RIJNDAEL_256,
        md5($sKey),
        base64_decode($sValue),
        MCRYPT_MODE_CBC,
        md5(md5($sKey))
      ), "\0");
    }
    
    public function fGetSessionId() {
      if (!isset($this->oUser))
        return '';
      $sHash = User::fHashPassword($this->oUser->fGetPasswordHash());
      return implode(';',
        array(
          $this->oUser->fGetUId(),
          $sHash
        )
      );
    }
    
    public function fGetUserObject() {
      return $this->oUser;
    }
    
    public function fSetSessionCookie() {
      if (!isset($this->oContext))
        throw new Exception('No HTTP context has been set yet');
      $sValue = $this->fGetSessionId();
      if (!empty($sValue)) $sValue = self::fEncryptCookieValue($sValue);
      $this->oContext->fSetResponseCookie(new HTTPCookie(
        self::SESSION_COOKIE_NAME, $sValue, 0, '/'
      ));
      return true;
    }
    
    public function fSetUserObjectByCookies() {
      if (!isset($this->oContext))
        throw new Exception('No HTTP context has been set yet');
      $this->oUser = null;
      $aCookies    = $this->oContext->fGetRequestCookies();
      $oCookie     = null;
      foreach ($aCookies as $oCookieIteration) {
        if ($oCookieIteration->fGetName() == self::SESSION_COOKIE_NAME) {
          $oCookie = $oCookieIteration;
          break;
        }
      }
      if (!isset($oCookie))
        return false;
      $sSessionValue = self::fDecryptCookieValue($oCookie->fGetValue());
      $aSession = explode(';', $sSessionValue);
      if (count($aSession) != 3)
        return false;
      $iUId          = (int)$aSession[0];
      $sPasswordHash = $aSession[1];
      $sSessionSalt  = $aSession[2];
      try {
        $oUser = new User($iUId);
      } catch (Exception $oError) {
        return false;
      }
      if (!$oUser) return false;
      $sHashOne = User::fHashPassword($oUser->fGetPasswordHash(), $sSessionSalt);
      $sHashTwo = $sPasswordHash;
      if ($sHashOne == $sHashTwo) {
        $this->oUser = $oUser;
        return true;
      } else {
        return false;
      }
    }
    
    public function fSetUserObjectByObject($oUser) {
      if (!is_null($oUser) && !($oUser instanceof User))
        throw new Exception('User is not of type null or User object');
      $this->oUser = $oUser;
      return true;
    }
    
  }