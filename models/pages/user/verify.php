<?php
  
  $aGetQuery    = $oContext->fGetRequestQueryArray();
  $aPostQuery   = $oContext->fGetRequestPostArray();
  $aQuery       = array_merge($aGetQuery, $aPostQuery);
  
  $bId          = (isset($aQuery['id']));
  $sId          = ($bId ? $aQuery['id'] : '');
  $iId          = ($bId ? (int)$sId     : null);
  
  $mResult      = false;
  $sFocusField  = "";
  
  if ($bId) {
    // User is at the second step: email received, needs to be verified.
    $oUser = User::fFindUserByVerifiedId($iId);
    if (!$oUser) {
      $mResult = "We could not find that identifier. It may be possible that the identifier was changed since you received your email. You should also ensure that the identifier has not been mistyped if you typed it in manually.";
    } else {
      $sFocusField = "username";
      if (!($oUser->fSetVerifiedDate(date('Y-m-d H:i:s')))) {
        BNETDocs::$oLogger->fLogEvent('user_verified', $oContext->fGetRequestIPAddress(), $oUser->fGetUId(), array('success' => false));
        $mResult = "A server error occurred while trying to save your account changes into our database.";
      } else {
        BNETDocs::$oLogger->fLogEvent('user_verified', $oContext->fGetRequestIPAddress(), $oUser->fGetUId(), array('success' => true));
        $mResult = true;
      }
    }
  }
  
  ob_start('ob_gzhandler');
  if (isset($aGetQuery['ajax'])) {
    include('./includes/user/verify-ajax.php');
  } else {
    include('./includes/user/verify.php');
  }
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  if ($aQuery)
    $oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  