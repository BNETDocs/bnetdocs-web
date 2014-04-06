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
     * Characters allowed to be in a username. This string is given
     * directly to php's preg_match() function. Be mindful that this
     * string may be displayed in more than just page content.
     **/
    const USERNAME_ALLOWED_CHARACTERS = "/^[A-Za-z0-9()}{\\[\\]._\\-]+$/s";
    
    /**
     * Characters allowed to be in a display name. This string is given
     * directly to php's preg_match() function. Be mindful that this
     * string may be displayed in more than just page content.
     **/
    const DISPLAYNAME_ALLOWED_CHARACTERS = "/^[ A-Za-z0-9()}{\\[\\]._\\-]+$/s";
    
    /**
     * Password strength requirements. If these are all set to false,
     * then there are NO requirements. If these are all set to true,
     * then there are VERY STRICT requirements.
     *
     * PASSWORD_REQUIRES_SYMBOLS is given directly to php's preg_match().
     **/
    const PASSWORD_CANNOT_CONTAIN_USERNAME    = true;
    const PASSWORD_CANNOT_CONTAIN_DISPLAYNAME = true;
    const PASSWORD_CANNOT_CONTAIN_EMAIL       = true;
    const PASSWORD_REQUIRES_UPPERCASE_LETTERS = true;
    const PASSWORD_REQUIRES_LOWERCASE_LETTERS = true;
    const PASSWORD_REQUIRES_NUMBERS           = true;
    const PASSWORD_REQUIRES_SYMBOLS           = false;
    /*const PASSWORD_REQUIRES_SYMBOLS           = "/['\":;^£$%&*()}{\\[\\]@#~\\?><>,.\\/|=_+¬\\-]/";*/
    
    /**
     * SQL column names for this object. Used in constructing new object.
     **/
    protected static $SQL_COLUMN_NAMES = [
      'uid',
      'email',
      'username',
      'display_name',
      // password_hash is excluded because it's a little more complex.
      'password_salt',
      'status',
      'registered_date',
      'verified_date',
      'verified_id',
    ];
    
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
    private $iVerifiedId;
    
    public static function fFindUsersByEmail($sEmail) {
      if (!is_string($sEmail))
        throw new Exception('Email address is not of type string');
      $sQuery = 'SELECT `' . implode('`,`', self::$SQL_COLUMN_NAMES) . '`,'
          . 'HEX(`password_hash`) AS `password_hash` FROM `users` WHERE `email` = \''
        . BNETDocs::$oDB->fEscapeValue($sEmail)
        . '\' ORDER BY `uid` ASC;';
      $oSQLResult = BNETDocs::$oDB->fQuery($sQuery);
      if (!$oSQLResult || !($oSQLResult instanceof SQLResult))
        throw new Exception('An SQL query error occurred while finding users by email');
      $aUsers = array();
      while ($oUser = $oSQLResult->fFetchObject()) {
        $aUsers[] = new self($oUser);
      }
      return $aUsers;
    }
    
    public static function fFindUserByUsername($sUsername) {
      if (!is_string($sUsername))
        throw new Exception('Username is not of type string');
      $sQuery = 'SELECT `' . implode('`,`', self::$SQL_COLUMN_NAMES) . '`,'
          . 'HEX(`password_hash`) AS `password_hash` FROM `users` WHERE `username` = \''
        . BNETDocs::$oDB->fEscapeValue($sUsername)
        . '\' LIMIT 1;';
      $oSQLResult = BNETDocs::$oDB->fQuery($sQuery);
      if (!$oSQLResult || !($oSQLResult instanceof SQLResult))
        throw new Exception('An SQL query error occurred while finding user by username');
      if ($oSQLResult->iNumRows != 1)
        return false;
      return new self($oSQLResult->fFetchObject());
    }
    
    public static function fFindUserByVerifiedId($iVerifiedId) {
      if (!is_numeric($iVerifiedId))
        throw new Exception('Verified Id is not of type numeric');
      $sQuery = 'SELECT `' . implode('`,`', self::$SQL_COLUMN_NAMES) . '`,'
          . 'HEX(`password_hash`) AS `password_hash` FROM `users` WHERE `verified_id` = \''
        . BNETDocs::$oDB->fEscapeValue($iVerifiedId)
        . '\' LIMIT 1;';
      $oSQLResult = BNETDocs::$oDB->fQuery($sQuery);
      if (!$oSQLResult || !($oSQLResult instanceof SQLResult))
        throw new Exception('An SQL query error occurred while finding user by verified id');
      if ($oSQLResult->iNumRows != 1)
        return false;
      return new self($oSQLResult->fFetchObject());
    }
    
    public function __construct() {
      $aFuncArgs = func_get_args();
      $iFuncArgs = count($aFuncArgs);
      if ($iFuncArgs == 1 && (is_numeric($aFuncArgs[0]) || is_object($aFuncArgs[0]))) {
        if (!is_object($aFuncArgs[0])) {
          // Create User object by result object. Need to get it by user id.
          $iUId = $aFuncArgs[0];
          $sQuery = 'SELECT `' . implode('`,`', self::$SQL_COLUMN_NAMES) . '`,'
            . 'HEX(`password_hash`) AS `password_hash` FROM `users`'
            . ' WHERE `uid` = \'' . BNETDocs::$oDB->fEscapeValue($iUId)
            . '\' LIMIT 1;';
          $oSQLResult = BNETDocs::$oDB->fQuery($sQuery);
          if (!$oSQLResult || !($oSQLResult instanceof SQLResult) || $oSQLResult->iNumRows != 1)
            throw new Exception('An SQL query error occurred while retrieving user by id');
          $oResult = $oSQLResult->fFetchObject();
        } else {
          // Create User object by result object. Object already gotten, no SQL query needed.
          $oResult = $aFuncArgs[0];
        }
        // CAUTION: May have to typecast here. Tried to avoid it by using fetch object.
        $this->iUId            = $oResult->uid;
        $this->sEmail          = $oResult->email;
        $this->sUsername       = $oResult->username;
        $this->sDisplayName    = $oResult->display_name;
        $this->sPasswordHash   = $oResult->password_hash;
        $this->iPasswordSalt   = $oResult->password_salt;
        $this->iStatus         = $oResult->status;
        $this->sRegisteredDate = $oResult->registered_date;
        $this->mVerifiedDate   = $oResult->verified_date;
        $this->iVerifiedId     = $oResult->verified_id;
      } else if ($iFuncArgs == 9) {
        $this->iUId            = null;
        $this->sEmail          = (string)$aFuncArgs[0];
        $this->sUsername       = (string)$aFuncArgs[1];
        $this->sDisplayName    = (string)$aFuncArgs[2];
        $this->sPasswordHash   = (string)$aFuncArgs[3];
        $this->iPasswordSalt   = (int)$aFuncArgs[4];
        $this->iStatus         = (int)$aFuncArgs[5];
        $this->sRegisteredDate = (string)$aFuncArgs[6];
        $this->mVerifiedDate   = $aFuncArgs[7];
        $this->iVerifiedId     = (int)$aFuncArgs[8];
      } else if ($iFuncArgs == 10) {
        $this->iUId            = (int)$aFuncArgs[0];
        $this->sEmail          = (string)$aFuncArgs[1];
        $this->sUsername       = (string)$aFuncArgs[2];
        $this->sDisplayName    = (string)$aFuncArgs[3];
        $this->sPasswordHash   = (string)$aFuncArgs[4];
        $this->iPasswordSalt   = (int)$aFuncArgs[5];
        $this->iStatus         = (int)$aFuncArgs[6];
        $this->sRegisteredDate = (string)$aFuncArgs[7];
        $this->mVerifiedDate   = $aFuncArgs[8];
        $this->iVerifiedId     = (int)$aFuncArgs[9];
      } else {
        throw new Exception('Wrong number of arguments given to constructor');
      }
    }
    
    public function fSave() {
      if (!isset($this->iUId) || is_null($this->iUId)) {
        $sQuery = 'INSERT INTO `users` ('
        . '`email`,'
        . '`username`,'
        . '`display_name`,'
        . '`password_hash`,'
        . '`password_salt`,'
        . '`status`,'
        . '`registered_date`,'
        . '`verified_date`,'
        . '`verified_id`'
        . ') VALUES (\''
        . BNETDocs::$oDB->fEscapeValue($this->sEmail) . '\',\''
        . BNETDocs::$oDB->fEscapeValue($this->sUsername) . '\',\''
        . BNETDocs::$oDB->fEscapeValue($this->sDisplayName) . '\',UNHEX(\''
        . BNETDocs::$oDB->fEscapeValue($this->sPasswordHash) . '\'),\''
        . BNETDocs::$oDB->fEscapeValue($this->iPasswordSalt) . '\',\''
        . BNETDocs::$oDB->fEscapeValue($this->iStatus) . '\',\''
        . BNETDocs::$oDB->fEscapeValue($this->sRegisteredDate) . '\',\''
        . BNETDocs::$oDB->fEscapeValue($this->mVerifiedDate) . '\',\''
        . BNETDocs::$oDB->fEscapeValue($this->iVerifiedId)
        . '\');';
      } else {
        $sQuery = 'UPDATE `users` SET '
        . '`email`=\'' . BNETDocs::$oDB->fEscapeValue($this->sEmail) . '\','
        . '`username`=\'' . BNETDocs::$oDB->fEscapeValue($this->sUsername) . '\','
        . '`display_name`=\'' . BNETDocs::$oDB->fEscapeValue($this->sDisplayName) . '\','
        . '`password_hash`=UNHEX(\'' . BNETDocs::$oDB->fEscapeValue($this->sPasswordHash) . '\'),'
        . '`password_salt`=\'' . BNETDocs::$oDB->fEscapeValue($this->iPasswordSalt) . '\','
        . '`status`=\'' . BNETDocs::$oDB->fEscapeValue($this->iStatus) . '\','
        . '`registered_date`=\'' . BNETDocs::$oDB->fEscapeValue($this->sRegisteredDate) . '\','
        . '`verified_date`=\'' . BNETDocs::$oDB->fEscapeValue($this->mVerifiedDate) . '\','
        . '`verified_id`=\'' . BNETDocs::$oDB->fEscapeValue($this->iVerifiedId) . '\' '
        . 'WHERE `uid`=\'' . BNETDocs::$oDB->fEscapeValue($this->iUId) . '\' LIMIT 1;';
      }
      if (BNETDocs::$oDB->fQuery($sQuery))
        return true;
      else
        return false;
    }
    
    public function fCheckPassword($sTargetPassword) {
      $sCurrentPasswordHash = $this->fGetPasswordHash();
      $iCurrentPasswordSalt = $this->fGetPasswordSalt();
      return false; // TODO: check password, return false/true.
    }
    
    public static function fGeneratePasswordSalt() {
      mt_srand(microtime(true)*100000 + memory_get_usage(true));
      return mt_rand(0, mt_getrandmax()) * 0xFFFFFFFF;
    }
    
    public static function fGenerateVerifiedId() {
      mt_srand(microtime(true)*100000 + memory_get_usage(true));
      return mt_rand(0, mt_getrandmax()) * 0xFFFFFFFF;
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
      return $this->iVerifiedId;
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
    
    public function fResetVerifiedId() {
      $iVerifiedId = self::fGenerateVerifiedId();
      return $this->fSetVerifiedId($iVerifiedId);
    }
    
    public function fSetEmail($sEmail) {
      if (!is_string($sEmail))
        throw new Exception('Email address is not of type string');
      if (empty($sEmail))
        throw new RecoverableException('Email address is an empty string');
      if (BNETDocs::$oDB->fQuery('UPDATE `users` SET `email` = \''
        . BNETDocs::$oDB->fEscapeValue($sEmail)
        . '\' WHERE `uid` = \''
        . BNETDocs::$oDB->fEscapeValue($this->iUId)
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
      if (BNETDocs::$oDB->fQuery('UPDATE `users` SET `username` = \''
        . BNETDocs::$oDB->fEscapeValue($sUsername)
        . '\' WHERE `uid` = \''
        . BNETDocs::$oDB->fEscapeValue($this->iUId)
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
      if (BNETDocs::$oDB->fQuery('UPDATE `users` SET `display_name` = \''
        . BNETDocs::$oDB->fEscapeValue($sDisplayName)
        . '\' WHERE `uid` = \''
        . BNETDocs::$oDB->fEscapeValue($this->iUId)
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
      $iPasswordSalt = self::fGeneratePasswordSalt();
      $sPasswordHash = self::fHashPassword($sPassword, $iPasswordSalt);
      if (BNETDocs::$oDB->fQuery('UPDATE `users` SET `password_hash` = UNHEX(\''
        . BNETDocs::$oDB->fEscapeValue($sPasswordHash)
        . '\'), `password_salt` = \''
        . BNETDocs::$oDB->fEscapeValue($iPasswordSalt)
        . '\' WHERE `uid` = \''
        . BNETDocs::$oDB->fEscapeValue($this->iUId)
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
      if (BNETDocs::$oDB->fQuery('UPDATE `users` SET `status` = \''
        . BNETDocs::$oDB->fEscapeValue($iStatus)
        . '\' WHERE `uid` = \''
        . BNETDocs::$oDB->fEscapeValue($this->iUId)
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
      if (BNETDocs::$oDB->fQuery('UPDATE `users` SET `registered_date` = \''
        . BNETDocs::$oDB->fEscapeValue($sRegisteredDate)
        . '\' WHERE `uid` = \''
        . BNETDocs::$oDB->fEscapeValue($this->iUId)
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
      if (BNETDocs::$oDB->fQuery('UPDATE `users` SET `verified_date` = '
        . (is_string($mVerifiedDate) ? '\''
        . BNETDocs::$oDB->fEscapeValue($mVerifiedDate)
        . '\'' : 'NULL')
        . ' WHERE `uid` = \''
        . BNETDocs::$oDB->fEscapeValue($this->iUId)
        . '\' LIMIT 1;'
      )) {
        $this->mVerifiedDate = $mVerifiedDate;
        return true;
      } else
        return false;
    }
    
    public function fSetVerifiedId($iVerifiedId) {
      if (!is_numeric($iVerifiedId))
        throw new Exception('Verified Id is not of type numeric');
      if (BNETDocs::$oDB->fQuery('UPDATE `users` SET `verified_id` = \''
        . BNETDocs::$oDB->fEscapeValue($iVerifiedId)
        . '\' WHERE `uid` = \''
        . BNETDocs::$oDB->fEscapeValue($this->iUId)
        . '\' LIMIT 1;'
      )) {
        $this->iVerifiedId = $iVerifiedId;
        return true;
      } else
        return false;
    }
    
  }
  
  