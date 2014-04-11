<?php
  
  $aGetQuery  = $oContext->fGetRequestQueryArray();
  $aPostQuery = $oContext->fGetRequestPostArray();
  $aQuery     = array_merge($aGetQuery, $aPostQuery);
  
  $bUsername  = (isset($aQuery['username']));
  $bId        = (isset($aQuery['id']));
  
  $sUsername  = ($bUsername ? $aQuery['username'] : '');
  $sId        = ($bId       ? $aQuery['id']       : '');
  
  $mResult    = false;
  
  if ($bUsername) {
    $oUser = User::fFindUserByUsername($sUsername);
    if (!$oUser) {
      $mResult = "Unable to locate that username in our database.";
    } else if (!$oUser->fResetVerifiedId()) {
      $mResult = "Failed to create a verification identifier for your account.";
    } else if (!Email::fSendPasswordReset($oUser)) {
      $mResult = "Failed to send a password reset email to your account.";
    } else {
      $mResult = true;
    }
  } else if ($bId) {
    $oUser = User::fFindUserByVerifiedId($iId);
    if (!$oUser) {
      $mResult = "We could not find that identifier. It may be possible that the identifier was changed since you received your email. Ensure that the identifier has not been mistyped.";
    } else {
      $mResult = true;
      BNETDocs::$oUser = $oUser;
      $oUser->fSetVerifiedDate(date('Y-m-d H:i:s'));
    }
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
  