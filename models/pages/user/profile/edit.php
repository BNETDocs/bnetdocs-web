<?php
  
  $aGetQuery   = $oContext->fGetRequestQueryArray();
  $aPostQuery  = $oContext->fGetRequestPostArray();
  $aQuery      = array_merge($aGetQuery, $aPostQuery);
  
  ob_start('ob_gzhandler');
  if (isset($aGetQuery['ajax'])) {
    include('./includes/user/profile/edit-ajax.php');
  } else {
    include('./includes/user/profile/edit.php');
  }
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  if ($aQuery)
    $oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  