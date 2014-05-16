<?php
  
  final class Contributors {
    
    private function __construct() {}
    
    /**
     * Show the top 5 users with the most contributed documents.
     **/
    public static function fGetTopDocumentAuthors() {
      $sQuery  = "SELECT COUNT(*) AS `document_count`, `author_uid` FROM `documents` GROUP BY `author_uid` ORDER BY `document_count` DESC LIMIT 5;";
      $oResult = BNETDocs::$oDB->fQuery($sQuery);
      if (!$oResult || !($oResult instanceof SQLResult)) return false;
      $aUsers = array();
      while ($oTopUser = $oResult->fFetchObject()) {
        if (is_null($oTopUser->author_uid)) {
          $aUsers[] = array($oTopUser->document_count, $oTopUser->author_uid);
        } else {
          $aUsers[] = array($oTopUser->document_count, new User($oTopUser->author_uid));
        }
      }
      return $aUsers;
    }
    
    /**
     * Show the top 5 users with the most contributed packets.
     **/
    public static function fGetTopPacketAuthors() {
      $sQuery  = "SELECT COUNT(*) AS `packet_count`, `author_uid` FROM `packets` GROUP BY `author_uid` ORDER BY `packet_count` DESC LIMIT 5;";
      $oResult = BNETDocs::$oDB->fQuery($sQuery);
      if (!$oResult || !($oResult instanceof SQLResult)) return false;
      $aUsers = array();
      while ($oTopUser = $oResult->fFetchObject()) {
        if (is_null($oTopUser->author_uid)) {
          $aUsers[] = array($oTopUser->packet_count, $oTopUser->author_uid);
        } else {
          $aUsers[] = array($oTopUser->packet_count, new User($oTopUser->author_uid));
        }
      }
      return $aUsers;
    }
    
    /**
     * Show the top 5 users with the most contributed servers.
     **/
    public static function fGetTopServerOwners() {
      $sQuery  = "SELECT COUNT(*) AS `server_count`, `owner_uid` FROM `servers` GROUP BY `owner_uid` ORDER BY `server_count` DESC LIMIT 5;";
      $oResult = BNETDocs::$oDB->fQuery($sQuery);
      if (!$oResult || !($oResult instanceof SQLResult)) return false;
      $aUsers = array();
      while ($oTopUser = $oResult->fFetchObject()) {
        if (is_null($oTopUser->owner_uid)) {
          $aUsers[] = array($oTopUser->server_count, $oTopUser->owner_uid);
        } else {
          $aUsers[] = array($oTopUser->server_count, new User($oTopUser->owner_uid));
        }
      }
      return $aUsers;
    }
    
  }
  