<?php
  
  class TrackedServer {
    
    /**
     * Server Statuses - The database only stores a tinyint field for this,
     * therefore, we have only 8 available flags to use.
     **/
    const STATUS_ONLINE   =   1;
    const STATUS_DISABLED =   2;
    const STATUS_UNUSED_1 =   4;
    const STATUS_UNUSED_2 =   8;
    const STATUS_UNUSED_3 =  16;
    const STATUS_UNUSED_4 =  32;
    const STATUS_UNUSED_5 =  64;
    const STATUS_UNUSED_6 = 128;
    
    private $iId;
    private $iOwnerUId;
    private $sDateAdded;
    private $sAddress;
    private $iPort;
    private $iType;
    private $iStatus;
    private $sDateUpdated;
    private $sLabel;
    
    public function __construct($iId) {
      // TODO: Get object contents from SQL.
    }
    
    public function fGetAddress() {
      return $this->sAddress;
    }
    
    public function fGetId() {
      return $this->iId;
    }
    
    public function fGetDateAdded() {
      // TODO: Use php DateTime class -- http://php.net/manual/en/class.datetime.php
      return $this->sDateAdded;
    }
    
    public function fGetLabel() {
      return $this->sLabel;
    }
    
    public function fGetOwnerUId() {
      return $this->iOwnerUId;
    }
    
    public function fGetPort() {
      return $this->iPort;
    }
    
    public function fGetType() {
      return $this->iType;
    }
    
    public function fGetStatus() {
      return $this->iStatus;
    }
    
    public function fGetDateUpdated() {
      // TODO: Use php DateTime class -- http://php.net/manual/en/class.datetime.php
      return $this->sDateUpdated;
    }
    
  }
  