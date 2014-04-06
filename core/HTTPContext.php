<?php
  
  class HTTPContext {
    
    protected $fRequestTimestamp;
    protected $bRequestSecure;
    protected $sRequestMethod;
    protected $sRequestURI;
    protected $sRequestPath;
    protected $sRequestQueryString;
    protected $aRequestQueryArray;
    protected $aRequestHeaders;
    protected $aRequestCookies;
    protected $aRequestPostArray;
    
    protected $iResponseCode;
    protected $aResponseHeaders;
    protected $aResponseCookies;
    protected $mResponseContent;
    
    function __construct() {
      
      if (\func_num_args() != 0)
        throw new \Exception('Constructor does not take any parameters');
      
      $this->fRequestTimestamp   = 0.0;
      $this->bRequestSecure      = false;
      $this->sRequestMethod      = '';
      $this->sRequestURI         = '';
      $this->sRequestPath        = '';
      $this->sRequestQueryString = '';
      $this->aRequestQueryArray  = array();
      $this->aRequestHeaders     = array();
      $this->aRequestCookies     = array();
      $this->aRequestPostArray   = array();
      
      $this->iResponseCode       = 500;
      $this->aResponseHeaders    = array();
      $this->aResponseCookies    = array();
      $this->mResponseContent    = '';
      
    }
    
    public function fGetRequestTimestamp() {
      return $this->fRequestTimestamp;
    }
    
    public function fGetRequestSecure() {
      return $this->bRequestSecure;
    }
    
    public function fGetRequestMethod() {
      return $this->sRequestMethod;
    }
    
    public function fGetRequestURI() {
      return $this->sRequestURI;
    }
    
    public function fGetRequestPath() {
      return $this->sRequestPath;
    }
    
    public function fGetRequestQueryString() {
      return $this->sRequestQueryString;
    }
    
    public function fGetRequestQueryArray() {
      return $this->aRequestQueryArray;
    }
    
    public function fGetRequestHeader($sName, $mDefault = false) {
      return (
        isset($this->aRequestHeaders[$sName]) ?
        $this->aRequestHeaders[$sName] :
        $mDefault
      );
    }
    
    public function fGetRequestHeaders() {
      return $this->aRequestHeaders;
    }
    
    public function fGetRequestCookies() {
      return $this->aRequestCookies;
    }
    
    public function fGetRequestPostArray() {
      return $this->aRequestPostArray;
    }
    
    public function fGetResponseCode() {
      return $this->iResponseCode;
    }
    
    public function fGetResponseHeaders() {
      return $this->aResponseHeaders;
    }
    
    public function fGetResponseCookies() {
      return $this->aResponseCookies;
    }
    
    public function fGetResponseContent() {
      return $this->mResponseContent;
    }
    
    public function fSetRequestByServerGlobals() {
      
      if (\func_num_args() != 0)
        throw new \Exception('This function does not take any parameters');
      
      if (!isset($_SERVER))
        throw new \Exception('$_SERVER variable is not set');
      
      $this->fRequestTimestamp   = \microtime(true);
      $this->bRequestSecure      = (BNETDocs::fGetServerPort() == 443);
      $this->sRequestMethod      = $_SERVER['REQUEST_METHOD'];
      $this->sRequestURI         = $_SERVER['REQUEST_URI'];
      $this->sRequestPath        = \parse_url(
        $this->sRequestURI,
        \PHP_URL_PATH
      );
      $this->sRequestQueryString = (
        isset($_SERVER['QUERY_STRING']) ?
        $_SERVER['QUERY_STRING'] :
        \parse_url($this->sRequestURI, \PHP_URL_QUERY)
      );
      $this->aRequestQueryArray  = $_GET;
      $this->aRequestHeaders     = BNETDocs::fTranslateArrayByKeyStart(
        $_SERVER,
        'HTTP_',
        true
      );
      $this->aRequestCookies     = HTTPCookie::fStringArrayToObjectArray(
        $_COOKIE
      );
      $this->aRequestPostArray   = $_POST;
      
      return true;
      
    }
    
    public function fSetResponseCode($iResponseCode) {
      if (!\is_numeric($iResponseCode))
        throw new \Exception('ResponseCode is not of type int');
      $this->iResponseCode = $iResponseCode;
      return true;
    }
    
    public function fSetResponseHeader($sName, $sValue) {
      if (!\is_string($sName))
        throw new \Exception('Name is not of type string');
      if (\strlen($sName) == 0)
        throw new \Exception('Name cannot be an empty string');
      if (!\is_string($sValue))
        throw new \Exception('Value is not of type string');
      $this->aResponseHeaders[$sName] = $sValue;
      return true;
    }
    
    public function fSetResponseHeaders(array $aResponseHeaders) {
      $this->aResponseHeaders = $aResponseHeaders;
      return true;
    }
    
    public function fSetResponseCookie(HTTPCookie $oCookie) {
      foreach ($this->aResponseCookies as $iIndex => $oPreCookie) {
        if ($oPreCookie->fGetName() === $oCookie->fGetName()) {
          unset($this->aResponseCookies[$iIndex]);
          break;
        }
      }
      $this->aResponseCookies[] = $oCookie;
      return true;
    }
    
    public function fSetResponseCookies(array $aResponseCookies) {
      $this->aResponseCookies = $aResponseCookies;
      return true;
    }
    
    public function fSetResponseContent($mResponseContent) {
      $this->mResponseContent = $mResponseContent;
      return true;
    }
    
  }
  