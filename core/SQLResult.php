<?php
  
  abstract class SQLResult {
    
    public $iCurrentField;
    public $iFieldCount;
    public $aLengths;
    public $iNumRows;
    
    public abstract function fDataSeek($iOffset);
    public abstract function fFetchAll($iResultType);
    public abstract function fFetchArray($iResultType);
    public abstract function fFetchAssoc();
    public abstract function fFetchFieldDirect($iFieldNR);
    public abstract function fFetchField();
    public abstract function fFetchFields();
    public abstract function fFetchObject($sClassName = '', $aParams = array());
    public abstract function fFetchRow();
    public abstract function fFieldSeek($iFieldNR);
    
  }