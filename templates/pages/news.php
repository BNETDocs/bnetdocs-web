<?php

  $oResult = BnetDocs::$oDB->fQuery('SELECT '
    . 'n.id AS `id`,'
    . 'IFNULL(u.display_name, u.username) AS `creator`,'
    . 'n.date_posted AS `date_posted`,'
    . 'n.edit_count AS `edit_count`,'
    . 'n.edit_date AS `edit_date`,'
    . 'n.title AS `title`,'
    . 'n.content AS `content` '
    . 'FROM news n '
    . 'LEFT JOIN users u '
    . 'ON n.creator_uid = u.id '
    . 'ORDER BY n.date_posted DESC '
    . 'LIMIT 0,3;');

  $aNews = array();
  if ($oResult && $oResult instanceof MySQLResult) {
    while ($aRow = $oResult->fFetchAssoc()) {
      $aNews[] = $aRow;
    }
  }
  
  ob_start('ob_gzhandler');
  include('./includes/news.php');
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  //$oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'text/html;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  