<?php
  
  /**
   * @package  BNETDocs
   * @version  Phoenix
   * @author   Carl Bennett
   */
  
  /**
   * @class  Packet
   * @brief  Provides an object-level representation of a packet.
   *
   * Dependencies
   * ------------
   * PacketApplicationLayer, PacketDirection, PacketTransportLayer
   **/
  class Packet {
    
    /**
     * @brief  Packet status bitfields.
     */
    const STATUS_DEFUNCT         = 1;
    const STATUS_NEEDS_RESEARCH  = 2;
    const STATUS_CONTROVERSIAL   = 4;
    
    private $iId;
    private $oAuthor;
    private $sAddedDate;
    private $iStatus;
    private $iEditCount;
    private $sEditDate;
    private $oTransportLayer;
    private $oApplicationLayer;
    private $iDirectionId;
    private $iPacketId;
    private $sPacketName;
    private $sPacketFormat;
    private $sPacketRemarks;
    
    /**
     * Constructs a new Packet object.
     * @param[in]  id            An id to retrieve from the database. This
     *                           should be the only parameter.
     * @param[in]  query_result  A raw object containing results from an
     *                           executed database query. This should be the
     *                           only parameter.
     * @details  Constructs a new Packet object. Examples:
     *               $oPacket = new Packet($iPacketIdInDatabase);
     *               $oPacket = new Packet($oPacketDataFromSQLResult);
     *               $oPacket = new Packet(each internal property, etc.);
     */
    public function __construct() {
      $aArgs   = func_get_args();
      $oResult = null;
      if (count($aArgs) == 1 && is_numeric($aArgs[0])) {
        // Construct new packet by id in database.
        $oResult = self::fFindByPacketId($aArgs[0]);
      } else if (count($aArgs) == 1 && is_object($aArgs[0])) {
        // Construct new packet by database result.
        $oResult = $aArgs[0];
      } else {
        // Construct new packet by internal reference.
        // (Probably creating a new packet to store into the database.)
        $oResult = new StdClass;
        $oResult->id                   = 0;
        $oResult->author_uid           = null;
        $oResult->added_date           = "0000-00-00 00:00:00.000000";
        $oResult->status               = 0;
        $oResult->edit_count           = 0;
        $oResult->edit_date            = null;
        $oResult->transport_layer_id   = 0;
        $oResult->application_layer_id = 0;
        $oResult->direction_id         = 0;
        $oResult->packet_id            = 0;
        $oResult->packet_name          = "";
        $oResult->packet_format        = "";
        $oResult->packet_remarks       = "";
        throw new RecoverableException("Not yet implemented");
      }
      // TODO: assign object variables based on $oResult object.
      try {
        $this->iId               = $oResult->id;
        $this->oAuthor           = new User($oResult->author_uid);
        $this->sAddedDate        = $oResult->added_date;
        $this->iStatus           = $oResult->status;
        $this->iEditCount        = $oResult->edit_count;
        $this->sEditDate         = $oResult->edit_date;
        $this->oTransportLayer   = new PacketTransportLayer($oResult->transport_layer_id);
        $this->oApplicationLayer = new PacketApplicationLayer($oResult->application_layer_id);
        $this->iDirectionId      = new PacketDirection($oResult->direction_id);
        $this->iPacketId         = $oResult->packet_id;
        $this->sPacketName       = $oResult->packet_name;
        $this->sPacketFormat     = $oResult->packet_format;
        $this->sPacketRemarks    = $oResult->packet_remarks;
      } catch (Exception $oException) {
        // We don't handle exceptions here. I just wanted to let you know
        // that an exception could happen by calling any of those constructors
        // above.
        throw $oException;
      }
    }
    
    public static function fFindByPacketId($iId) {
      if (!is_numeric($iId))
        throw new RecoverableException("Packet id is not of type numeric");
      $sQuery = "SELECT * FROM `packets` WHERE `id` = '" . BNETDocs::$oDB->fEscapeValue($iId) . "' LIMIT 1;";
      $mQuery = BNETDocs::$oDB->fQuery($sQuery);
      if (!$mQuery || !($mQuery instanceof SQLResult)) return false;
      $oResult = $mQuery->fFetchObject();
      return new self($oResult);
    }
    
    public function fGetAddedDate() {
      return $this->sAddedDate;
    }
    
    public function fGetApplicationLayer() {
      return $this->oApplicationLayer;
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
    
    public function fGetId() {
      return $this->iId;
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
    
    public function fGetStatus() {
      return $this->iStatus;
    }
    
    public function fGetTransportLayer() {
      return $this->oTransportLayer;
    }
    
  }
  