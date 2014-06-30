<?php
  
  class Packet {
    
    /**
     * Packet status bitfields.
     **/
    const STATUS_DEFUNCT         = 1;
    const STATUS_NEEDS_RESEARCH  = 2;
    const STATUS_CONTROVERSIAL   = 4;
    
    /**
     * Direction ids
     **/
    const DIRECTION_CLIENT_SERVER = 0;
    const DIRECTION_SERVER_CLIENT = 1;
    const DIRECTION_PEER_TO_PEER  = 2;
    
    private $iPId;
    private $oAuthor;
    private $sAddedDate;
    private $iStatus;
    private $iEditCount;
    private $sEditDate;
    private $iProtocolGroup;
    private $iNetworkLayerId;
    private $iDirectionId;
    private $iPacketId;
    private $sPacketName;
    private $sPacketFormat;
    private $sPacketRemarks;
    
    public function __construct() {
      $aArgs = func_get_args();
      if (count($aArgs) == 1 && is_numeric($aArgs[0])) {
        $oResult = self::fFindByPacketId($aArgs[0]);
      } else if (count($aArgs) == 1 && is_object($aArgs[0])) {
        $oResult = $aArgs[0];
      } else {
        // Construct new packet?
        throw new RecoverableException("Not yet implemented");
      }
      // TODO: assign object variables based on $aArgs[0] object.
    }
    
    public static function fFindByPacketId($iPId) {
      if (!is_numeric($iPId))
        throw new RecoverableException("Packet id is not of type numeric");
      $sQuery = "SELECT * FROM `packets` WHERE `pid` = '" . BNETDocs::$oDB->fEscapeValue($iPId) . "' LIMIT 1;";
      $mQuery = BNETDocs::$oDB->fQuery($sQuery);
      if (!$mQuery || !($mQuery instanceof SQLResult)) return false;
      $oResult = $mQuery->fFetchObject();
      return new self($oResult);
    }
    
    public function fGetAddedDate() {
      return $this->sAddedDate;
    }
    
    public function fGetAuthor() {
      return $this->oAuthor;
    }
    
    public function fGetDirectionId() {
      return $this->iDirectionId;
    }
    
    public function fGetEditCount() {
      return $this->iEditCount;
    }
    
    public function fGetEditDate() {
      return $this->sEditDate;
    }
    
    public function fGetPacketFormat() {
      return $this->sPacketFormat;
    }
    
    public function fGetPacketId() {
      return $this->iPacketId;
    }
    
    public function fGetPacketName() {
      return $this->sPacketName;
    }
    
    public function fGetPacketRemarks() {
      return $this->sPacketRemarks;
    }
    
    public function fGetPId() {
      return $this->iPId;
    }
    
    public function fGetStatus() {
      return $this->iStatus;
    }
    
  }
  