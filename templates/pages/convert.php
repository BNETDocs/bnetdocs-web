<?php

 function Codify($text){
                $patterns[0] = "|\[b\](.*?)\[/b\]|s";
                $patterns[1] = "|\[i\](.*?)\[/i\]|s";
                $patterns[2] = "|\[u\](.*?)\[/u\]|s";
                $patterns[3] = "|\[center\](.*?)\[/center\]|s";
                $patterns[4] = "|\[url\](.*?)\[/url\]|s";
                $patterns[5] = "|\[url=(.*?)\](.*?)\[/url\]|s";
                $patterns[6] = "|\[img\](http://.*?)\[/img\]|s";
                $patterns[7] = "|\[img\]([0-9]+)(\.[a-zA-Z0-9]{0,10})\[/img\]|s";
                $patterns[8] = "|\[code\](.*?)\[/code\]|s";
                $patterns[10] = "|\[s\](.*?)\[/s\]|s";
                $replacements[0] = "<b>\$1</b>";
                $replacements[1] = "<i>\$1</i>";
                $replacements[2] = "<u>\$1</u>";
                $replacements[3] = "<center>\$1</center>";
                $replacements[4] = "<a href=\"\$1\">\$1</a>";
                $replacements[5] = "<a href=\"\$1\">\$2</a>";
                $replacements[6] = "<img src=\"\$1\" />";
                $replacements[7] = "<img src=\"images/\$1\$2\" />";
                $replacements[8] = "<div style=\"overflow: auto;\" align=center><div id=\"code\"><pre>\$1</pre></div></div>";
                $replacements[10] = "<strike>\$1</strike>";
                ksort($patterns);
                ksort($replacements);
                $text = preg_replace($patterns, $replacements, $text);
                $text = nl2brex($text);
                return $text;
        }
  
  $oResult = false;
  $aNews   = array();
  
  $oResult = BnetDocs::$oDB->fQuery('SELECT id,content FROM news_posts;');
  $oResults = array();
  
  if ($oResult && $oResult instanceof MySQLResult) {
    while ($aRow = $oResult->fFetchAssoc()) {
      $oResults[] = BnetDocs::$oDB->fQuery('UPDATE news_posts SET content = \''
        . BnetDocs::$oDB->fEscapeValue(Codify($aRow['content'])) . '\' WHERE '
        . 'id=\'' . BnetDocs::$oDB->fEscapeValue($aRow['id']) . '\' LIMIT 1;');
      $aNews[] = $aRow;
    }
  }
  
  ob_start('ob_gzhandler');
  print_r($oResults);
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  //$oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  