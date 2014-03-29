<?php
  
  final class User {
    
    /**
     * The hashing algorithm to use. Ensure the users table in the
     * database allows for the exact length that the algorithm gives.
     * (ex.: sha256 yields 256 bits which means a binary(32) field.)
     **/
    const PASSWORD_HASH_ALGORITHM = 'sha256';
    
    /**
     * Password length requirements are irrespective of the database
     * design, since passwords are salted hashes.
     **/
    const PASSWORD_LENGTH_MINIMUM = 6;
    const PASSWORD_LENGTH_MAXIMUM = 48;
    
    /**
     * Internal class variables used for storing info.
     **/
    private $iUId;
    private $sEmail;
    private $sUsername;
    private $sDisplayName;
    private $sPasswordHash;
    private $iPasswordSalt;
    private $iStatus;
    private $sRegisteredDate;
    private $mVerifiedDate;
    private $sVerifiedId;
    
    public static function fFindUsersByEmail($sEmail) {
      if (!is_string($sEmail))
        throw new Exception('Email address is not of type string');
      $sQuery = 'SELECT `id` FROM `users` WHERE `email` = \''
        . BnetDocs::$oDB->fEscapeValue($sEmail)
        . '\' ORDER BY `id` ASC;';
      $oSQLResult = BnetDocs::$oDB->fQuery($sQuery);
      if (!$oSQLResult || !($oSQLResult instanceof SQLResult))
        throw new Exception('An SQL query error occurred while finding users by email');
      return ($oSQLResult->fRowCount() ? true : false);
    }
    
    public static function fFindUserByUsername($sUsername) {
      if (!is_string($sUsername))
        throw new Exception('Username is not of type string');
      $sQuery = 'SELECT `id` FROM `users` WHERE `username` = \''
        . BnetDocs::$oDB->fEscapeValue($sUsername)
        . '\' LIMIT 1;';
      $oSQLResult = BnetDocs::$oDB->fQuery($sQuery);
      if (!$oSQLResult || !($oSQLResult instanceof SQLResult) || $oSQLResult->iNumRows != 1)
        throw new Exception('An SQL query error occurred while finding user by username');
      return new self((int)$oSQLResult->fFetchObject()->id);
    }
    
    public function __construct($iUId) {
      $aFields = array(
        'uid',
        'email',
        'username',
        'display_name',
        'password_salt',
        'status',
        'registered_date',
        'verified_date',
        'verified_id',
      );
      $sQuery = 'SELECT `' . implode('`,`', $aFields) . '`,'
        . 'HEX(`password_hash`) AS `password_hash` FROM `users`'
        . ' WHERE `uid` = \'' . BnetDocs::$oDB->fEscapeValue($iUId)
        . '\' LIMIT 1;';
      $oSQLResult = BnetDocs::$oDB->fQuery($sQuery);
      if (!$oSQLResult || !($oSQLResult instanceof SQLResult) || $oSQLResult->iNumRows != 1)
        throw new Exception('An SQL query error occurred while retrieving user by id');
      $oResult = $oSQLResult->fFetchObject();
      // CAUTION: May have to typecast here. Tried to avoid it by using fetch object.
      $this->iUId             = $oResult->uid;
      $this->sEmail          = $oResult->email;
      $this->sUsername       = $oResult->username;
      $this->sDisplayName    = $oResult->display_name;
      $this->sPasswordHash   = $oResult->password_hash;
      $this->iPasswordSalt   = $oResult->password_salt;
      $this->iStatus         = $oResult->status;
      $this->sRegisteredDate = $oResult->registered_date;
      $this->mVerifiedDate   = $oResult->verified_date;
      $this->sVerifiedId     = $oResult->verified_id;
    }
    
    public function fCheckPassword($sTargetPassword) {
      $sCurrentPasswordHash = $this->fGetPasswordHash();
      $iCurrentPasswordSalt = $this->fGetPasswordSalt();
      return false; // TODO: check password, return false/true.
    }
    
    public function fGetId() {
      return $this->iUId;
    }
    
    public function fGetEmail() {
      return $this->sEmail;
    }
    
    public function fGetUsername() {
      return $this->sUsername;
    }
    
    public function fGetDisplayName() {
      return $this->sDisplayName;
    }
    
    public function fGetPasswordHash() {
      return $this->sPasswordHash;
    }
    
    public function fGetPasswordSalt() {
      return $this->iPasswordSalt;
    }
    
    public function fGetStatus() {
      return $this->iStatus;
    }
    
    public function fGetRegisteredDate() {
      return $this->sRegisteredDate;
    }
    
    public function fGetVerifiedDate() {
      return $this->mVerifiedDate;
    }
    
    public function fGetVerifiedId() {
      return $this->sVerifiedId;
    }
    
    public static function fHashPassword($sPassword, $iSalt) {
      if (!is_string($sPassword))
        throw new Exception('Password is not of type string');
      return hash(
        self::PASSWORD_HASH_ALGORITHM,
        hash(
          self::PASSWORD_HASH_ALGORITHM,
          $sPassword,
          false
        ) . (string)$iSalt . 'bnetdocs+db~$!',
        false
      );
    }
    
    public function fSetEmail($sEmail) {
      if (!is_string($sEmail))
        throw new Exception('Email address is not of type string');
      if (empty($sEmail))
        throw new RecoverableException('Email address is an empty string');
      if (BnetDocs::$oDB->fQuery('UPDATE `users` SET `email` = \''
        . BnetDocs::$oDB->fEscapeValue($sEmail)
        . '\' WHERE `uid` = \''
        . $this->iUId
        . '\' LIMIT 1;'
      )) {
        $this->sEmail = $sEmail;
        return true;
      } else
        return false;
    }
    
    public function fSetUsername($sUsername) {
      if (!is_string($sUsername))
        throw new Exception('Username is not of type string');
      if (empty($sUsername))
        throw new RecoverableException('Username is an empty string');
      if (BnetDocs::$oDB->fQuery('UPDATE `users` SET `username` = \''
        . BnetDocs::$oDB->fEscapeValue($sUsername)
        . '\' WHERE `uid` = \''
        . $this->iUId
        . '\' LIMIT 1;'
      )) {
        $this->sUsername = $sUsername;
        return true;
      } else
        return false;
    }
    
    public function fSetDisplayName($sDisplayName) {
      if (!is_string($sDisplayName))
        throw new Exception('Display Name is not of type string');
      if (empty($sDisplayName))
        throw new RecoverableException('Display Name is an empty string');
      if (BnetDocs::$oDB->fQuery('UPDATE `users` SET `display_name` = \''
        . BnetDocs::$oDB->fEscapeValue($sDisplayName)
        . '\' WHERE `uid` = \''
        . $this->iUId
        . '\' LIMIT 1;'
      )) {
        $this->sDisplayName = $sDisplayName;
        return true;
      } else
        return false;
    }
    
    public function fSetPassword($sPassword) {
      if (!is_string($sPassword))
        throw new Exception('Password is not of type string');
      $iPasswordLength = strlen($sPassword);
      if ($iPasswordLength < self::PASSWORD_LENGTH_MINIMUM && self::PASSWORD_LENGTH_MINIMUM > 0)
        throw new RecoverableException('Password is less than ' . self::PASSWORD_LENGTH_MINIMUM . ' characters');
      if ($iPasswordLength > self::PASSWORD_LENGTH_MAXIMUM && self::PASSWORD_LENGTH_MAXIMUM >= self::PASSWORD_LENGTH_MINIMUM)
        throw new RecoverableException('Password is more than ' . self::PASSWORD_LENGTH_MAXIMUM . ' characters');
      mt_srand(microtime(true)*100000 + memory_get_usage(true));
      $iPasswordSalt = mt_rand(0, 0xFFFFFFFFFFFFFFFF);
      $sPasswordHash = self::fHashPassword($sPassword, $iPasswordSalt);
      if (BnetDocs::$oDB->fQuery('UPDATE `users` SET `password_hash` = UNHEX(\''
        . BnetDocs::$oDB->fEscapeValue($sPasswordHash)
        . '\'), `password_salt` = \''
        . BnetDocs::$oDB->fEscapeValue($iPasswordSalt)
        . '\' WHERE `uid` = \''
        . $this->iUId
        . '\' LIMIT 1;'
      )) {
        $this->sPasswordHash = $sPasswordHash;
        $this->iPasswordSalt = $iPasswordSalt;
        return true;
      } else
        return false;
    }
    
    public function fSetStatus($iStatus) {
      if (!is_numeric($iStatus))
        throw new Exception('Status is not of type numeric');
      if (BnetDocs::$oDB->fQuery('UPDATE `users` SET `status` = \''
        . BnetDocs::$oDB->fEscapeValue($iStatus)
        . '\' WHERE `uid` = \''
        . $this->iUId
        . '\' LIMIT 1;'
      )) {
        $this->iStatus = $iStatus;
        return true;
      } else
        return false;
    }
    
    public function fSetRegisteredDate($sRegisteredDate) {
      if (!is_string($sRegisteredDate))
        throw new Exception('Registered Date is not of type string');
      if (empty($sRegisteredDate))
        throw new RecoverableException('Registered Date is an empty string');
      if (BnetDocs::$oDB->fQuery('UPDATE `users` SET `registered_date` = \''
        . BnetDocs::$oDB->fEscapeValue($sRegisteredDate)
        . '\' WHERE `uid` = \''
        . $this->iUId
        . '\' LIMIT 1;'
      )) {
        $this->sRegisteredDate = $sRegisteredDate;
        return true;
      } else
        return false;
    }
    
    public function fSetVerifiedDate($mVerifiedDate) {
      if (!is_string($mVerifiedDate) && !is_null($mVerifiedDate))
        throw new Exception('Verified Date is not of type string or null');
      if (is_string($mVerifiedDate) && empty($mVerifiedDate))
        throw new RecoverableException('Verified Date is an empty string');
      if (BnetDocs::$oDB->fQuery('UPDATE `users` SET `verified_date` = '
        . (is_string($mVerifiedDate) ? '\''
        . BnetDocs::$oDB->fEscapeValue($mVerifiedDate)
        . '\'' : 'NULL')
        . ' WHERE `uid` = \''
        . $this->iUId
        . '\' LIMIT 1;'
      )) {
        $this->mVerifiedDate = $mVerifiedDate;
        return true;
      } else
        return false;
    }
    
    public function fSetVerifiedId($sVerifiedId) {
      if (!is_string($sVerifiedId))
        throw new Exception('Verified Id is not of type string');
      if (empty($sVerifiedId))
        throw new RecoverableException('Verified Id is an empty string');
      if (BnetDocs::$oDB->fQuery('UPDATE `users` SET `verified_id` = \''
        . BnetDocs::$oDB->fEscapeValue($sVerifiedId)
        . '\' WHERE `uid` = \''
        . $this->iUId
        . '\' LIMIT 1;'
      )) {
        $this->sVerifiedId = $sVerifiedId;
        return true;
      } else
        return false;
    }
    
  }
  
  