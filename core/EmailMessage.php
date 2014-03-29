<?php
  
  class EmailMessage {
    
    private $sContentType;
    private $sBody;
    
    public function __construct($sContentType, $sBody) {
      $this->fSetContentType($sContentType);
      $this->fSetBody($sBody);
    }
    
    public function fGetContentType() {
      return $this->sContentType;
    }
    
    public function fGetBody() {
      return $this->sBody;
    }
    
    public function fSetContentType($sContentType) {
      if (!is_string($sContentType))
        throw new Exception('ContentType is not of type string');
      $this->sContentType = $sContentType;
      return true;
    }
    
    public function fSetBody($sBody) {
      if (!is_string($sBody))
        throw new Exception('Body is not of type string');
      $this->sBody = $sBody;
      return true;
    }
    
  }
  