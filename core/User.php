<?php
  
  class User {
    
    private $iId;
    private $sEmail;
    private $sUsername;
    private $sPasswordHash;
    private $iPasswordSalt;
    private $iStatus;
    
    public static function fFindUsersByEmail($sEmail) {
      if (!is_string($sEmail))
        throw new Exception('Email address is not of type string');
      $sQuery = 'SELECT `id` FROM `users` WHERE `email` = \''
        . BnetDocs::$oDB->fEscapeValue($sEmail)
        . '\' ORDER BY `id` ASC;';
      $oSQLResult = BnetDocs::$oDB->fQuery($sQuery);
      if (!$oSQLResult)
        throw new Exception('An SQL query error occurred while finding a user by id');
      return ($oSQLResult->fRowCount() ? true : false);
    }
    
    public static function fFindUserByUsername($sUsername) {
      if (!is_string($sUsername))
        throw new Exception('Username is not of type string');
      $sQuery = 'SELECT `id` FROM `users` WHERE `username` = \''
        . BnetDocs::$oDB->fEscapeValue($sUsername)
        . '\' LIMIT 1;';
      $oSQLResult = BnetDocs::$oDB->fQuery($sQuery);
      if (!$oSQLResult)
        throw new Exception('An SQL query error occurred while finding a user by id');
      return new self((int)$oSQLResult->fFieldValue());
    }
    
    public function __construct($iId) {
      $this->iId = $iId;
    }
    
    public function fCheckPassword($sTargetPassword) {
      $sCurrentPasswordHash = $this->fGetPasswordHash();
      $iCurrentPasswordSalt = $this->fGetPasswordSalt();
      
    }
    
    public function fHashPassword($sPassword, $iSalt) {
      if (!is_string($sPassword))
        throw new Exception('Password is not of type string');
      return sha1(sha1($sPassword) . (string)$iSalt . 'bnetdocs+db~$!');
    }
    
    public function fSetEmail($sEmail) {
      if (!is_string($sEmail))
        throw new Exception('Email address is not of type string');
      if (empty($sEmail))
        throw new RecoverableException('Email address is an empty string');
      $this->aNewCollection['email'] = $sEmail;
      return true;
    }
    
    public function fSetUsername($sUsername) {
      if (!is_string($sUsername))
        throw new Exception('Username is not of type string');
      if (empty($sUsername))
        throw new RecoverableException('Username is an empty string');
      $this->aNewCollection['username'] = $sUsername;
      return true;
    }
    
    public function fSetPassword($sPassword) {
      if (!is_string($sPassword))
        throw new Exception('Password is not of type string');
      if (empty($sPassword))
        throw new RecoverableException('Password is an empty string');
      $this->aNewCollection['password_hash'] = $this->fHashPassword($sPassword, $this->fGetPasswordSalt());
      return true;
    }
    
  }
  
  