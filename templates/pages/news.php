<?php

  $oResult = BnetDocs::$oDB->fQuery('SELECT n.id, u.username, n.date_posted, n.edit_count, n.edit_date, n.title, n.content FROM news n LEFT JOIN users u ON n.creator_uid = u.id ORDER BY n.date_posted DESC LIMIT 0,3;');

  if (!$oResult || !$oResult instanceof MySQLResult) {
    $aNews = array();
  } else {
    $aNews = array(
      0 => array(
        'id' => 0,
        'username' => 'hardcoded-test',
        'date_posted' => date('Y-m-d H:i:s T'),
        'edit_count' => 1,
        'edit_date' => date('Y-m-d H:i:s T'),
        'title' => 'hardcoded test title',
        'content' => 'this is a hardcoded test',
      ),
    );
  }
  
  ob_start('ob_gzhandler');
  include_once('./includes/news.php');
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  //$oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'text/html;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  