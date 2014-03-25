<?php

  class XMLEncoder {
    
    public static $bAddTypeAttributes = true;
    public static $sInvalidKeyAttributeName = 'key'; // Blank means to not add the attribute
    public static $sInvalidKeyName = ''; // Blank means to inherit sibling key
    
    public static function fEncode($sRootName, $aData, $bPrettyPrint = false) {
      
      if (!$sRootName || $sRootName == '') return false;
      if (!self::fValidKey($sRootName)) return false;
      
      $oRoot = new SimpleXMLElement(
        '<?xml version="1.0" encoding="UTF-8"?><' . $sRootName . '/>'
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
    
    private static function fEncoder(&$oNode, $aData) {
      
      if (is_null($oNode))
        throw new Exception('Node is of type null');
      
      foreach ($aData as $sKey => $mValue) {
        
        if (array_key_exists($sKey, $aData) && is_array($mValue) && count($mValue) == 0) {
          // value is an empty array, add "<key/>" to node:
          self::fEncoderAddKey($oNode, $sKey);
        }
        
        if (is_array($mValue) && !self::fIsAssocArray($mValue)) {
          
          // It is not a key-pair array.
          foreach ($mValue as $mVal) {
            if (is_array($mVal)) {
              $oSubNode = self::fEncoderAddKey($oNode, $sKey);
              self::fEncoder($oSubNode, $mVal);
            } else {
              $oSubNode = self::fEncoderAddKey($oNode, $sKey, $mVal);
            }
          }
          
        } else if (is_array($mValue)) {
          
          // It is a key-pair array.
          if (count($mValue) > 0) {
            // Only append more elements if non-empty.
            $oSubNode = self::fEncoderAddKey($oNode, $sKey);
            self::fEncoder($oSubNode, $mValue);
          }
          
        } else {
          
          // It's not an array, and...
          
          if (!array_key_exists($sKey, $aData)) {
            // ...we're not operating on a key-pair array; key == value.
            self::fEncoderAddKey($oNode, $sKey);
          } else {
            // ...the value is not an array either.
            self::fEncoderAddKey($oNode, $sKey, $mValue);
          }
          
        }
        
      }
      
    }
    
    private static function fEncoderAddKey(&$oNode, $sKey) {
      
      if (func_num_args() >= 3) {
        $mValue = func_get_arg(2);
        if (!self::fValidKey($sKey)) {
          $sKeyName = self::$sInvalidKeyName;
          if (!$sKeyName) $sKeyName = $oNode->getName();
          $oSubNode = $oNode->addChild($sKeyName, self::fEncodeKey($mValue));
          $sKeyAttributeName = self::$sInvalidKeyAttributeName;
          if ($sKeyAttributeName) {
            $oSubNode->addAttribute($sKeyAttributeName, self::fEncodeKey($sKey));
          }
        } else {
          $oSubNode = $oNode->addChild($sKey, self::fEncodeKey($mValue));
        }
        if (self::$bAddTypeAttributes) {
          $oSubNode->addAttribute('type', self::fEncodeKey(gettype($mValue)));
        }
      } else {
        if (!self::fValidKey($sKey)) {
          $sKeyName = self::$sInvalidKeyName;
          if (!$sKeyName) $sKeyName = $oNode->getName();
          $oSubNode = $oNode->addChild($sKeyName);
          $sKeyAttributeName = self::$sInvalidKeyAttributeName;
          if ($sKeyAttributeName) {
            $oSubNode->addAttribute($sKeyAttributeName, self::fEncodeKey($sKey));
          }
        } else {
          $oSubNode = $oNode->addChild($sKey);
        }
        if (self::$bAddTypeAttributes) {
          $oSubNode->addAttribute('type', 'array');
        }
      }
      
      return $oSubNode;
      
    }
    
    private static function fEncodeKey($sKey) {
      // ENT_XML1 only exists in PHP versions >= 5.4.
      if (PHP_VERSION >= 5.4) {
        return htmlspecialchars($sKey, ENT_XML1, 'UTF-8');
      } else {
        return htmlspecialchars($sKey, ENT_QUOTES, 'UTF-8');
      }
    }
    
    private static function fIsAssocArray($aValue) {
      foreach ($aValue as $sKey => $mValue)
      {
        if (!is_numeric($sKey)) return true;
      }
      return false;
    }
    
    private static function fValidKey($sKey) {
      //$sMask = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_';
      //if (strpos($sMask, substr($key, 0, 1)) === false) return false;
      return (preg_match('/\A(?!XML)[a-z][\w0-9-]*/i', $sKey));
    }
  }
  