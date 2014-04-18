<?php
  
  $aGetQuery    = $oContext->fGetRequestQueryArray();
  $aPostQuery   = $oContext->fGetRequestPostArray();
  $aQuery       = array_merge($aGetQuery, $aPostQuery);
  
  $bUsername    = (isset($aQuery['username']));
  $bId          = (isset($aQuery['id']));
  $bPasswordOne = (isset($aQuery['password_1']));
  $bPasswordTwo = (isset($aQuery['password_2']));
  
  $sUsername    = ($bUsername    ? $aQuery['username']   : '');
  $sId          = ($bId          ? $aQuery['id']         : '');
  $iId          = ($bId          ? (int)$sId             : null);
  $sPasswordOne = ($bPasswordOne ? $aQuery['password_1'] : '');
  $sPasswordTwo = ($bPasswordTwo ? $aQuery['password_2'] : '');
  
  $mResult      = false;
  $sFocusField  = "";
  
  if ($bUsername && !($bId || $bPasswordOne || $bPasswordTwo)) {
    // User is at the first step: no email yet.
    $oUser = User::fFindUserByUsername($sUsername);
    if (!$oUser) {
      $mResult = "Unable to locate that username in our database. Check to make sure you entered your username correctly.";
    } else if (!$oUser->fResetVerifiedId()) {
      $mResult = "Failed to create a verification identifier for your account. "
               . "Email us at <a href=\"mailto:" . Email::$oBNETDocsRecipient->fGetAddress() . "\">" . Email::$oBNETDocsRecipient->fGetAddress() . "</a> "
               . "to have us manually activate your account.";
    } else if (!Email::fSendPasswordReset($oUser)) {
      $mResult = "Failed to send a password reset email to your account. "
               . "Email us at <a href=\"mailto:" . Email::$oBNETDocsRecipient->fGetAddress() . "\">" . Email::$oBNETDocsRecipient->fGetAddress() . "</a> "
               . "to have us manually activate your account.";
    } else {
      $mResult = true;
    }
    $sFocusField = "username";
  } else if ($bId && !($bUsername)) {
    // User is at the second step: email received, needs new password entered.
    $oUser = User::fFindUserByVerifiedId($iId);
    if (!$oUser) {
      $mResult = "We could not find that identifier. It may be possible that the identifier was changed since you received your email. You should also ensure that the identifier has not been mistyped if you typed it in manually.";
    } else if (!($bPasswordOne && $bPasswordTwo)) {
    } else {
      $sUsername    = $oUser->fGetUsername();
      $sDisplayName = $oUser->fGetDisplayName();
      $sEmail       = $oUser->fGetEmail();
      $mCheckForm   = User::fCheckForm($sUsername, $sDisplayName, $sPasswordOne, $sPasswordTwo, $sEmail, $sEmail, false, false, true, false);
      if ($mCheckForm !== true) {
        $sFocusField = "password_1";
        $mResult     = $mCheckForm;
      } else {
        BNETDocs::$oUserSession->fSetUserObjectByObject($oUser);
        BNETDocs::$oUserSession->fSetSessionCookie();
        $sFocusField     = "username";
        if (!($oUser->fSetVerifiedDate(date('Y-m-d H:i:s')) && $oUser->fSetPassword($sPasswordOne))) {
          $mResult = "A server error occurred while trying to save your account changes into our database.";
        } else {
          $mResult = true;
        }
      }
    }
  } else if ($bId && !($bPasswordOne || $bPasswordTwo) && !($bUsername)) {
    $sFocusField = "password_1";
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
  