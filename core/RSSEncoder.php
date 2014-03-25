<?php
  
  final class RSSEncoder extends XMLEncoder {
    
    public static $bAddTypeAttributes = false;
    public static $sInvalidKeyAttributeName = 'id'; // Blank means to not add the attribute
    public static $sInvalidKeyName = 'item'; // Blank means to inherit sibling key
    
    public static function fEncode($aData, $sRootName = 'rss', $bPrettyPrint = false) {
      
      $oRoot = new SimpleXMLElement(
        '<?xml version="1.0" encoding="UTF-8"?>'.
        '<' . $sRootName . ' version="2.0" />'
      );
      
      self::fEncoder($oRoot, $aData);
      
      if (!$bPrettyPrint) {
        return $oRoot->asXML();
      } else {
        $oDom = new DOMDocument("1.0");
        $oDom->preserveWhiteSpace = false;
        $oDom->formatOutput = true;
        $oDom->loadXML($oRoot->asXML());
        return $oDom->saveXML();
      }
      
    }
    
  }
  