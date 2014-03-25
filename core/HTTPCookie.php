<?php
  
  class HTTPCookie {
    
    protected $sName;
    protected $sValue;
    protected $iExpire;
    protected $sPath;
    protected $sDomain;
    protected $bSecure;
    protected $bHTTPOnly;
    
    public function __construct() {
      
      $aArgs = \func_get_args();
      $iArgs = \func_num_args();
      
      if ($iArgs < 1)
        throw new \Exception('Constructor takes at least 1 parameter');
      if ($iArgs > 7)
        throw new \Exception('Constructor takes at most 7 parameters');
      
      $this->fSetName((isset($aArgs[0]) ? $aArgs[0] : ''));
      $this->fSetValue((isset($aArgs[1]) ? $aArgs[1] : ''));
      $this->fSetExpire((isset($aArgs[2]) ? $aArgs[2] : 0));
      $this->fSetPath((isset($aArgs[3]) ? $aArgs[3] : ''));
      $this->fSetDomain((isset($aArgs[4]) ? $aArgs[4] : ''));
      $this->fSetSecure((isset($aArgs[5]) ? $aArgs[5] : false));
      $this->fSetHTTPOnly((isset($aArgs[6]) ? $aArgs[6] : false));
      
    }
    
    public function __toString() {
      $sCookie = '';
      
      $sCookie .= $this->fGetName();
      $sCookie .= '=';
      $sCookie .= $this->fGetValue();
      
      if ($this->fGetExpire() != 0)
        $sCookie .= '; Expires=' . date('D, j M Y H:i:s T', $this->fGetExpire());
      
      if (\strlen($this->fGetPath()) > 0)
        $sCookie .= '; Path=' . $this->fGetPath();
      
      if (\strlen($this->fGetDomain()) > 0)
        $sCookie .= '; Domain=' . $this->fGetDomain();
      
      if ($this->fGetSecure())
        $sCookie .= '; Secure';
      
      if ($this->fGetHTTPOnly())
        $sCookie .= '; HttpOnly';
      
      return $sCookie;
    }
    
    public function fGetName() {
      return $this->sName;
    }
    
    public function fGetValue() {
      return $this->sValue;
    }
    
    public function fGetExpire() {
      return $this->iExpire;
    }
    
    public function fGetPath() {
      return $this->sPath;
    }
    
    public function fGetDomain() {
      return $this->sDomain;
    }
    
    public function fGetSecure() {
      return $this->bSecure;
    }
    
    public function fGetHTTPOnly() {
      return $this->bHTTPOnly;
    }
    
    public function fSetName($sName) {
      if (!\is_string($sName))
        throw new \Exception('Name is not of type string');
      if (\strlen($sName) == 0)
        throw new \Exception('Name cannot be an empty string');
      $this->sName = $sName;
    }
    
    public function fSetValue($sValue) {
      if (!\is_string($sValue))
        throw new \Exception('Value is not of type string');
      $this->sValue = $sValue;
    }
    
    public function fSetExpire($iExpire) {
      if (!\is_numeric($iExpire))
        throw new \Exception('Expire is not of type number');
      $this->iExpire = $iExpire;
    }
    
    public function fSetPath($sPath) {
      if (!\is_string($sPath))
        throw new \Exception('Path is not of type string');
      $this->sPath = $sPath;
    }
    
    public function fSetDomain($sDomain) {
      if (!\is_string($sDomain))
        throw new \Exception('Domain is not of type string');
      $this->sDomain = $sDomain;
    }
    
    public function fSetSecure($bSecure) {
      if (!\is_bool($bSecure))
        throw new \Exception('Secure is not of type bool');
      $this->bSecure = $bSecure;
    }
    
    public function fSetHTTPOnly($bHTTPOnly) {
      if (!\is_bool($bHTTPOnly))
        throw new \Exception('HTTPOnly is not of type bool');
      $this->bHTTPOnly = $bHTTPOnly;
    }
    
    public static function fStringArrayToObjectArray(array &$aRawCookies) {
      
      $aCookies = array();
      
      foreach ($aRawCookies as $sName => $sValue) {
        $aCookies[] = new HTTPCookie($sName, $sValue);
      }
      
      return $aCookies;
      
    }
    
  }
  