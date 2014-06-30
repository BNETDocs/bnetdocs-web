<?php
  
  if (!BNETDocs::$oUserSession) {
    $sRedirectURL = BNETDocs::fGetCurrentFullURL('/');
    $sSafeRedirectURL = urlencode($sRedirectURL);
    
    ob_start('ob_gzhandler');
    include('./includes/redirect.php');
    $sRedirectPage = ob_get_clean();
    
    $oContext->fSetResponseCode(307);
    $oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
    $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
    $oContext->fSetResponseHeader('Location', $sRedirectURL);
    $oContext->fSetResponseContent($sRedirectPage);
    
    return;
  }
  
  $aGetQuery   = $oContext->fGetRequestQueryArray();
  $aPostQuery  = $oContext->fGetRequestPostArray();
  $aQuery      = array_merge($aGetQuery, $aPostQuery);
  
  $sUsername    = BNETDocs::$oUserSession->fGetUserObject()->fGetUsername();
  $sDisplayName = BNETDocs::$oUserSession->fGetUserObject()->fGetDisplayName();
  
  $sFocusField = "display_name";
  
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
  