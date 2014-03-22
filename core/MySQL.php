<?php
  
  class MySQL extends SQL {
    
    protected $oLink;
    
    public function __construct() {
      parent::__construct();
      
      $this->oLink = mysqli_init();
    }
    
    public function fClose() {
      $this->oLink->close();
    }
    
    public function fConnect() {
      $sHostname = $this->fGetHostname();
      $iPort = 3306;
      $iPos = strpos($sHostname, ':');
      if ($iPos) {
        $iPort = substr($sHostname, 0, $iPos - 1);
        $sHostname = substr($sHostname, $iPos + 1);
      }
      $oLink->options(MYSQLI_OPT_CONNECT_TIMEOUT, $this->fGetConnectTimeout());
      $bConnectedState = $oLink->real_connect(
        $sHostname,
        $this->fGetUsername(),
        $this->fGetPassword(),
        $this->fGetDatabase(),
        $iPort
      );
      if ($bConnectedState) {
        $this->oLink->set_charset($this->fGetCharacterSet());
      }
      return $bConnectedState;
    }
    
    public function fErrorMessage() {
      return $this->oLink->error;
    }
    
    public function fErrorNumber() {
      return $this->oLink->errno;
    }
    
    public function fEscapeValue($mValue) {
      return $this->oLink->real_escape_string($mValue);
    }
    
    public function fQuery($sQuery) {
      return new MySQLResult($this->oLink->query($sQuery, MYSQLI_STORE_RESULT));
    }
    
  }
  