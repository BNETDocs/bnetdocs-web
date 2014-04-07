<?php
  
  $aGetQuery  = $oContext->fGetRequestQueryArray();
  $aPostQuery = $oContext->fGetRequestPostArray();
  $aQuery     = array_merge($aGetQuery, $aPostQuery);
  
  $sId = (isset($aQuery['id']) ? $aQuery['id'] : '');
  $iId = (int)$sId;
  
  $sPasswordResetFailed  = "";
  $bPasswordResetSuccess = false;
  
  $oUser = User::fFindUserByVerifiedId($iId);
  if (!$oUser) {
    $sPasswordResetFailed = "We could not find that identifier. It may be possible that the identifier was changed since you received your email. Ensure that the identifier has not been mistyped.";
  } else {
    $bPasswordResetSuccess = true;
    BNETDocs::$oUser = $oUser;
    $oUser->fSetVerifiedDate(date('Y-m-d H:i:s'));
  }
  
  ob_start('ob_gzhandler');
  if (isset($aGetQuery['ajax'])) {
    include('./includes/user/password_reset-ajax.php');
  } else {
    include('./includes/user/password_reset.php');
  }
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  if ($aQuery)
    $oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  