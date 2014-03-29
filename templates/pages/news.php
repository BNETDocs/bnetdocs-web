<?php
  
  $oResult = false;
  $aNews   = array();
  
  $oResult = BnetDocs::$oDB->fQuery('SELECT '
    . 'n.id AS `id`,'
    . 'IFNULL(u.display_name, u.username) AS `creator`,'
    . 'n.post_date AS `post_date`,'
    . 'n.edit_count AS `edit_count`,'
    . 'n.edit_date AS `edit_date`,'
    . 'n.title AS `title`,'
    . 'n.content AS `content` '
    . 'FROM news_posts n '
    . 'LEFT JOIN users u '
    . 'ON n.creator_uid = u.uid '
    . 'ORDER BY n.post_date DESC '
    . 'LIMIT 0,3;');
  
  if ($oResult && $oResult instanceof MySQLResult) {
    while ($aRow = $oResult->fFetchAssoc()) {
      $aNews[] = $aRow;
    }
  } else {
    $aNews[] = array(
      'id'         => 0,
      'creator'    => 'n/a',
      'post_date'  => date('Y-m-d H:i:s T'),
      'edit_count' => 0,
      'edit_date'  => null,
      'title'      => 'ERROR RETRIEVING NEWS',
      'content'    => 'An error has occurred while retrieving the news.',
    );
  }
  
  ob_start('ob_gzhandler');
  include('./includes/news.php');
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  //$oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'text/html;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  