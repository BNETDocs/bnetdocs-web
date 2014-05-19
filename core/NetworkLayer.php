<?php
  
  class NetworkLayer {
    
    private $iId;
    private $sDisplayName;
    
    public function __construct($iId, $sDisplayName) {
      $this->fSetId($iId);
      $this->fSetDisplayName($sDisplayName);
    }
    
    public function fGetDisplayName() {
      return $this->sDisplayName;
    }
    
    public static function fGetNetworkLayerById($iId) {
      if (!is_numeric($iId))
        throw new RecoverableException("Id is not of type numeric");
      $sQuery = "SELECT `id`, `display_name` FROM `packet_network_layers` WHERE `id` = '" . BNETDocs::$oDB->fEscapeValue($iId) . "' LIMIT 1;";
      $mQuery = BNETDocs::$oDB->fQuery($sQuery);
      if (!$mQuery || !($mQuery instanceof SQLResult)) return false;
      $oResult = $mQuery->fFetchObject();
      return new self($oResult->id, $oResult->display_name);
    }
    
    public static function fGetNetworkLayers() {
      $sQuery = "SELECT `id`, `display_name` FROM `packet_network_layers` ORDER BY `id` ASC;";
      $mQuery = BNETDocs::$oDB->fQuery($sQuery);
      if (!$mQuery || !($mQuery instanceof SQLResult)) return false;
      $oResults = array();
      while ($oResult = $mQuery->fFetchObject()) {
        $oResults[] = new self($oResult->id, $oResult->display_name);
      }
      return $oResults;
    }
    
    public function fGetId() {
      return $this->iId;
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
    
  }
  