<?php
  
  class ProtocolGroup {
    
    private $iId;
    private $sDisplayName;
    private $sPrefix;
    
    public function __construct($iId, $sDisplayName, $sPrefix) {
      $this->fSetId($iId);
      $this->fSetDisplayName($sDisplayName);
      $this->fSetPrefix($sPrefix);
    }
    
    public function fGetDisplayName() {
      return $this->sDisplayName;
    }
    
    public function fGetId() {
      return $this->iId;
    }
    
    public function fGetPrefix() {
      return $this->sPrefix;
    }
    
    public static function fGetProtocolGroupById($iId) {
      if (!is_numeric($iId))
        throw new RecoverableException("Id is not of type numeric");
      $sQuery = "SELECT `id`, `display_name`, `prefix` FROM `packet_protocol_groups` WHERE `id` = '" . BNETDocs::$oDB->fEscapeValue($iId) . "' LIMIT 1;";
      $mQuery = BNETDocs::$oDB->fQuery($sQuery);
      if (!$mQuery || !($mQuery instanceof SQLResult)) return false;
      $oResult = $mQuery->fFetchObject();
      return new self($oResult->id, $oResult->display_name, $oResult->prefix);
    }
    
    public static function fGetProtocolGroups() {
      $sQuery = "SELECT `id`, `display_name`, `prefix` FROM `packet_protocol_groups` ORDER BY `id` ASC;";
      $mQuery = BNETDocs::$oDB->fQuery($sQuery);
      if (!$mQuery || !($mQuery instanceof SQLResult)) return false;
      $oResults = array();
      while ($oResult = $mQuery->fFetchObject()) {
        $oResults[] = new self($oResult->id, $oResult->display_name, $oResult->prefix);
      }
      return $oResults;
    }
    
    public function fSetDisplayName($sDisplayName) {
      if (!is_string($sDisplayName))
        throw new Exception('Display Name is not of type string');
      $this->sDisplayName = $sDisplayName;
      return true;
    }
    
    public function fSetId($iId) {
      if (!is_numeric($iId))
        throw new Exception('Id is not of type numeric');
      $this->iId = $iId;
      return true;
    }
    
    public function fSetPrefix($sPrefix) {
      if (!is_string($sPrefix))
        throw new Exception('Prefix is not of type string');
      $this->sPrefix = $sPrefix;
      return true;
    }
    
  }
  