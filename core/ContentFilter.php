<?php
  
  class ContentFilter {
    
    private function __construct() {}
    
    public static function fFilterHTML($sContent) {
      
      if (!is_string($sContent))
        throw new Exception('Content is not of type string');
      
      $sParsedContent = $sContent;
      
      $sParsedContent = htmlspecialchars($sParsedContent, ENT_QUOTES | ENT_DISALLOWED);
      $sParsedContent = preg_replace('/\r\n|\r|\n+/', "<br/>\n", trim($sParsedContent));
      
      return $sParsedContent;
      
    }
    
    public static function fFilterBBCode($sContent) {
      
      if (!is_string($sContent))
        throw new Exception('Content is not of type string');
      
      $sParsedContent = $sContent;
      
      // [b]PARAM[/b]
      $sParsedContent = preg_replace(
        '/\[b](.+?)\[\/b]/is',
        '<strong>$1</strong>',
        $sParsedContent
      );
      
      // [i]PARAM[/i]
      $sParsedContent = preg_replace(
        '/\[i](.+?)\[\/i]/is',
        '<em>$1</em>',
        $sParsedContent
      );
      
      // [u]PARAM[/u]
      $sParsedContent = preg_replace(
        '/\[u](.+?)\[\/u]/is',
        '<span style="text-decoration:underline;">$1</span>',
        $sParsedContent
      );
      
      // [url]PARAM[/url] or [url=PARAM]LABEL[/url]
      $sParsedContent = preg_replace(
        '~\[url(?|=[\'"]?+([^]"\']++)[\'"]?+]([^[]++)|](([^[]++)))\[/url]~',
        '<a href="$1">$2</a>',
        $sParsedContent
      );
      
      return $sParsedContent;
      
    }
    
  }
  