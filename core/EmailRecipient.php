<?php
  
  class EmailRecipient {
    
    const TYPE_FROM = 0;
    const TYPE_TO   = 1;
    const TYPE_CC   = 2;
    const TYPE_BCC  = 3;
    
    private $sAddress;
    private $iType;
    private $sName;
    private $bPrefersPlaintext;
    
    public function __construct($sAddress) {
      $this->fSetAddress($sAddress);
      $this->fSetType((func_num_args() > 1 ? func_get_arg(1) : self::TYPE_TO));
      $this->fSetName((func_num_args() > 2 ? func_get_arg(2) : ''));
      $this->fSetPrefersPlaintext((func_num_args() > 3 ? func_get_arg(3) : ''));
    }
    
    public function __toString() {
      /**
       * The intent is to generate an RFC 2822-compliant email address.
       * http://www.faqs.org/rfcs/rfc2822.html
       **/
      if (empty($this->sName))
        return htmlspecialchars($this->sAddress, ENT_QUOTES, 'UTF-8');
      else
        return htmlspecialchars($this->sName, ENT_QUOTES, 'UTF-8') .
          ' <' . htmlspecialchars($this->sAddress, ENT_QUOTES, 'UTF-8') . '>';
    }
    
    public function fGetAddress() {
      return $this->sAddress;
    }
    
    public function fGetName() {
      return $this->sName;
    }
    
    public function fGetPrefersPlaintext() {
      return $this->bPrefersPlaintext;
    }
    
    public function fGetType() {
      return $this->iType;
    }
    
    public function fSetAddress($sAddress) {
      if (!is_string($sAddress))
        throw new Exception('Address is not of type string');
      $this->sAddress = $sAddress;
      return true;
    }
    
    public function fSetName($sName) {
      if (!is_string($sName))
        throw new Exception('Name is not of type string');
      $this->sName = $sName;
      return true;
    }
    
    public function fSetPrefersPlaintext($bPrefersPlaintext) {
      if (!is_bool($bPrefersPlaintext))
        throw new Exception('PrefersPlaintext is not of type bool');
      $this->bPrefersPlaintext = $bPrefersPlaintext;
      return true;
    }
    
    public function fSetType($iType) {
      if (!is_numeric($iType))
        throw new Exception('Type is not of type numeric');
      $this->iType = $iType;
      return true;
    }
    
  }
  