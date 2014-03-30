<?php
  
  class ContentFilter {
    
    private function __construct() {}
    
    public static function fFilterHTML($sContent, $bAllowTags = false) {
      
      if (!is_string($sContent))
        throw new Exception('Content is not of type string');
      
      $sParsedContent = $sContent;
      
      $sParsedContent = htmlspecialchars($sParsedContent, ENT_QUOTES | ENT_DISALLOWED | ENT_XHTML, 'UTF-8');
      
      if ($bAllowTags) {
        $sParsedContent = str_replace(
          array('&lt;', '&gt;', '&quot;', '&apos;'),
          array('<', '>', '"', "'"),
          $sParsedContent
        );
      }
      
      return $sParsedContent;
      
    }
    
    public static function fFilterNewLines($sContent) {
      
      if (!is_string($sContent))
        throw new Exception('Content is not of type string');
      
      $sParsedContent = $sContent;
      
      $sParsedContent = preg_replace('/\r\n|\r|\n+/', "<br/>\n", trim($sParsedContent));
      
      return $sParsedContent;
      
    }
    
  }
  