<?php
  
  class Logger {
    
    private $aLogTypes;
    
    public function fGetTypeIdByName($sTypeName) {
      foreach ($this->aLogTypes as $oLogType) {
        if ($oLogType->fGetTypeName() == $sTypeName) {
          return $oLogType->fGetTypeId();
        }
      }
      return false;
    }
    
    public function fLoadLogTypes() {
      $sQuery  = 'SELECT `type_id`, `type_name`, `display_name` FROM `log_types` ORDER BY `type_id` ASC;';
      $oResult = BNETDocs::$oDB->fQuery($sQuery);
      if (!$oResult || !($oResult instanceof SQLResult))
        throw new RecoverableException('An SQL error occurred while retrieving log types');
      unset($this->aLogTypes);
      while ($oRow = $oResult->fFetchObject()) {
        $this->aLogTypes[] = new LogType($oRow->type_id, $oRow->type_name, $oRow->display_name);
      }
      return true;
    }
    
    public function fLogEvent($mLogType, $iAuthorUId, $mContent) {
      $sEventDate = date('Y-m-d H:i:s.000000');
      
      if (is_string($mLogType)) {
        $iTypeId = self::fGetTypeIdByName($mLogType);
      } else if (is_numeric($mLogType) && $mLogType >= 0) {
        $iTypeId = $mLogType;
      } else {
        $iTypeId = false;
      }
      
      if (!$iTypeId)
        throw new Exception('Cannot find a type id for that log type');
      
      if (is_string($mContent) || $mContent instanceof HTTPContext) {
        $sContent = $mContent;
      } else {
        throw new Exception('Content is not of type string or HTTPContext object');
      }
      
      $sQuery = 'INSERT INTO `logs` (`type_id`, `author_uid`, `event_date`, `content`) VALUES ('
              . '\'' . BNETDocs::$oDB->fEscapeValue($iTypeId) . '\','
              . (is_null($iAuthorUId) ? 'NULL' : '\'' . BNETDocs::$oDB->fEscapeValue($iAuthorUId) . '\'') . ','
              . '\'' . BNETDocs::$oDB->fEscapeValue($sEventDate) . '\','
              . '\'' . BNETDocs::$oDB->fEscapeValue($sContent) . '\''
              . ');';
      
      $mResult = BNETDocs::$oDB->fQuery($sQuery);
      if (!$mResult || !($mResult instanceof SQLResult)) {
        return false;
      } else {
        return true;
      }
    }
    
  }
  