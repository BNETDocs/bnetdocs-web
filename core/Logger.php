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
    
    public function fLogEvent($mLogType, $sRemoteAddress, $iAuthorUId, $mContent) {
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
      
      if (!is_null($sRemoteAddress) && !is_string($sRemoteAddress))
        throw new Exception('Remote Address is not of type null or numeric');
      
      if (!empty($sRemoteAddress) && strpos($sRemoteAddress, ':') !== false) {
        $sIPv4Address = null;
        $sIPv6Address = BNETDocs::fNormalizeIP($sRemoteAddress);
      } else if (!empty($sRemoteAddress) && strpos($sRemoteAddress, '.') !== false) {
        $sIPv4Address = BNETDocs::fNormalizeIP($sRemoteAddress);
        $sIPv6Address = null;
      } else {
        $sIPv4Address = null;
        $sIPv6Address = null;
      }
      
      if (!is_null($iAuthorUId) && !is_numeric($iAuthorUId))
        throw new Exception('Author UId is not of type null or numeric');
      
      if (is_string($mContent) || $mContent instanceof HTTPContext) {
        $sContent = $mContent;
      } else if (is_array($mContent) {
        $sContent = json_encode($mContent);
      } else {
        throw new Exception('Content is not of type string, array, or HTTPContext object');
      }
      
      $sQuery = 'INSERT INTO `logs` (`type_id`, `author_uid`, `author_ipv4`, `author_ipv6`, `event_date`, `content`) VALUES ('
              . '\'' . BNETDocs::$oDB->fEscapeValue($iTypeId) . '\','
              . (is_null($iAuthorUId) ? 'NULL' : '\'' . BNETDocs::$oDB->fEscapeValue($iAuthorUId) . '\'') . ','
              . (is_null($sIPv4Address) ? 'NULL' : 'CONV(\'' . BNETDocs::$oDB->fEscapeValue($sIPv4Address) . '\',16,10)') . ','
              . (is_null($sIPv6Address) ? 'NULL' : 'UNHEX(\'' . BNETDocs::$oDB->fEscapeValue($sIPv6Address) . '\')') . ','
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
  