<?php
  
  class MySQLResult extends SQLResult {
    
    public $iCurrentField;
    public $iFieldCount;
    public $aLengths;
    public $iNumRows;
    
    private $oResource;
    
    public function __construct($oResource) {
      if (!($oResource instanceof mysqli_result))
        throw new Exception('Wrong resource object type given to MySQLResult constructor');
      
      $this->oResource     = $oResource;
      $this->iCurrentField = $oResource->$current_field;
      $this->iFieldCount   = $oResource->$field_count;
      $this->aLengths      = $oResource->$lengths;
      $this->iNumRows      = $oResource->$num_rows;
    }
    
    public function __destruct() {
      $oResource->free();
    }
    
    public function fDataSeek($iOffset) {
      return $this->oResource->data_seek($iOffset);
    }
    
    public function fFetchAll($iResultType) {
      return $this->oResource->fetch_all($iResultType);
    }
    
    public function fFetchArray($iResultType) {
      return $this->oResource->fetch_array($iResultType);
    }
    
    public function fFetchAssoc() {
      return $this->oResource->fetch_assoc();
    }
    
    public function fFetchFieldDirect($iFieldNR) {
      return $this->oResource->fetch_field_direct($iFieldNR);
    }
    
    public function fFetchField() {
      return $this->oResource->fetch_field();
    }
    
    public function fFetchFields() {
      return $this->oResource->fetch_fields();
    }
    
    public function fFetchObject($sClassName = '', $aParams = array()) {
      if (!empty($sClassName) && count($aParams))
        return $this->oResource->fetch_object($sClassName, $aParams);
      else if (!empty($sClassName))
        return $this->oResource->fetch_object($sClassName);
      else
        return $this->oResource->fetch_object();
    }
    
    public function fFetchRow() {
      return $this->oResource->fetch_row();
    }
    
    public function fFieldSeek($iFieldNR) {
      return $this->oResource->field_seek($iFieldNR);
    }
    
  }