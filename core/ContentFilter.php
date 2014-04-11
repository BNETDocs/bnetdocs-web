<?php
  
  class ContentFilter {
    
    private function __construct() {}
    
    public static function fTrimArticleContent($sContent, $iSentences = 2) {
      $i = 0;
      $j = 0;
      $k = 0;
      do {
        $j = strpos($sContent, '.', $i + 1);
        if ($j) {
          $i = $j;
          ++$k;
        }
      } while ($k < $iSentences);
      $sEllpsis = ($i + 1 < strlen($sContent) ? '..' : '');
      return substr($sContent, 0, $i + 1) . $sEllpsis;
    }
    
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
    
    public static function fFilterNewLines($sContent, $bRemoveLineCharacters = false, $sReplacePattern = "<br/>\n") {
      
      if (!is_string($sContent))
        throw new Exception('Content is not of type string');
      
      $sParsedContent = $sContent;
      
      $sParsedContent = preg_replace('/\r\n|\r|\n+/', $sReplacePattern, trim($sParsedContent));
      
      if ($bRemoveLineCharacters)
        $sParsedContent = str_replace("\n", "", $sParsedContent);
      
      return $sParsedContent;
      
    }
    
  }
  