<?php
  
  $aGetQuery   = $oContext->fGetRequestQueryArray();
  $aPostQuery  = $oContext->fGetRequestPostArray();
  $aQuery      = array_merge($aGetQuery, $aPostQuery);
  
  $bCSRFToken  = (isset($aQuery['csrf']));
  $bLogout     = (isset($aQuery['logout']));
  
  $sCSRFToken  = ($bCSRFToken ? $aQuery['csrf'] : '');
  
  $mResult     = false;
  
  if (!BNETDocs::$oUserSession || is_null(BNETDocs::$oUserSession->fGetUserObject())) {
    $mResult = "You are not currently logged in. <a href=\"/user/login\">Click here</a> to log in to your account.";
  } else if ($bCSRFToken && $bLogout) {
    if (!AntiCSRF::fCheckToken($sCSRFToken)) {
      $mResult = "Cross-Site Request Forgery detected. Try logging out again.";
    } else {
      BNETDocs::$oUserSession->fSetUserObjectByObject(null);
      BNETDocs::$oUserSession->fSetSessionCookie();
      $mResult = true;
    }
  }
  
  ob_start('ob_gzhandler');
  if (isset($aGetQuery['ajax'])) {
    include('./includes/user/logout-ajax.php');
  } else {
    include('./includes/user/logout.php');
  }
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  if ($aQuery)
    $oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  