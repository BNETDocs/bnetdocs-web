<?php
  
  class LogType {
    
    private $iTypeId;
    private $sTypeName;
    private $sDisplayName;
    
    public function __construct($iTypeId, $sTypeName, $sDisplayName) {
      $this->fSetTypeId($iTypeId);
      $this->fSetTypeName($sTypeName);
      $this->fSetDisplayName($sDisplayName);
    }
    
    public function fGetDisplayName() {
      return $this->sDisplayName;
    }
    
    public function fGetTypeId() {
      return $this->iTypeId;
    }
    
    public function fGetTypeName() {
      return $this->sTypeName;
    }
    
    public function fSetDisplayName($sDisplayName) {
      if (!is_string($sDisplayName))
        throw new Exception('Display Name is not of type string');
      $this->sDisplayName = $sDisplayName;
      return true;
    }
    
    public function fSetTypeId($iTypeId) {
      if (!is_numeric($iTypeId))
        throw new Exception('Type Id is not of type numeric');
      $this->iTypeId = $iTypeId;
      return true;
    }
    
    public function fSetTypeName($sTypeName) {
      if (!is_string($sTypeName))
        throw new Exception('Type Name is not of type string');
      $this->sTypeName = $sTypeName;
      return true;
    }
    
  }
  