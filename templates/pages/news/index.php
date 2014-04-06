<?php
  
  $sNewsId   = basename($oContext->fGetRequestPath());
  $iNewsId   = (int)$sNewsId;
  $aArticle  = array();
  $aComments = array();
  
  if ($sNewsId == "") {
    $sQueryString = $oContext->fGetRequestQueryString();
    if (!empty($sQueryString)) $sQueryString = '?' . $sQueryString;
    
    $sRedirectURL = BNETDocs::fGetCurrentFullURL('/news' . $sQueryString);
    $sSafeRedirectURL = urlencode($sRedirectURL);
    
    ob_start('ob_gzhandler');
    include('./includes/redirect.php');
    $sRedirectPage = ob_get_clean();
    
    $oContext->fSetResponseCode(302);
    $oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
    $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
    $oContext->fSetResponseHeader('Location', $sRedirectURL);
    $oContext->fSetResponseContent($sRedirectPage);
  } else {
    
    $oArticleResult = BNETDocs::$oDB->fQuery('SELECT '
      . 'n.`id` AS `id`,'
      . 'IFNULL(u.`display_name`, u.`username`) AS `creator`,'
      . 'IFNULL(n.`edit_date`, n.`post_date`) AS `pub_date`,'
      . 'n.`edit_count` AS `edit_count`,'
      . 'c.`id` AS `category_id`,'
      . 'c.`display_name` AS `category_name`,'
      . 'n.`title` AS `title`,'
      . 'n.`content` AS `content` '
      . 'FROM `news_posts` n '
      . 'LEFT JOIN `users` u '
      . 'ON n.`creator_uid` = u.`uid` '
      . 'LEFT JOIN `news_categories` c '
      . 'ON n.`category` = c.`id` '
      . 'WHERE n.`id` = \'' . BNETDocs::$oDB->fEscapeValue($iNewsId) . '\' '
      . 'ORDER BY `pub_date` DESC, n.`id` DESC '
      . 'LIMIT 1;');
    if ($oArticleResult && $oArticleResult instanceof SQLResult)
      $aArticle = $oArticleResult->fFetchAssoc();
    
    $oCommentsResult = BNETDocs::$oDB->fQuery('SELECT '
      . 'c.`id` AS `id`,'
      . 'IFNULL(u.`display_name`, u.`username`) AS `creator`,'
      . 'IFNULL(c.`edit_date`, c.`comment_date`) AS `pub_date`,'
      . 'c.`edit_count` AS `edit_count`,'
      . 'c.`content` AS `content` '
      . 'FROM `news_comments` c '
      . 'LEFT JOIN `users` u '
      . 'ON c.`author_uid` = u.`uid` '
      . 'WHERE c.`post_id` = \'' . BNETDocs::$oDB->fEscapeValue($iNewsId) . '\' '
      . 'ORDER BY `pub_date` ASC, c.`id` ASC;');
    if ($oCommentsResult && $oCommentsResult instanceof SQLResult) {
      while ($aRow = $oCommentsResult->fFetchAssoc()) {
        $aComments[] = $aRow;
      }
    }
    
    $oContext->fSetResponseCode(200);
    
    ob_start('ob_gzhandler');
    if (isset($aGetQuery['ajax'])) {
      include('./includes/news-article-ajax.php');
    } else {
      include('./includes/news-article.php');
    }
    $sPage = ob_get_clean();
    
    $oContext->fSetResponseHeader('Cache-Control', 'max-age=300');
    $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
    $oContext->fSetResponseContent($sPage);
  }
  