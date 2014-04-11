<?php
  
  $aGetQuery   = $oContext->fGetRequestQueryArray();
  $aPostQuery  = $oContext->fGetRequestPostArray();
  $aQuery      = array_merge($aGetQuery, $aPostQuery);
  
  $bUsername   = (isset($aQuery['username']));
  $bPassword   = (isset($aQuery['password']));
  
  $sUsername   = ($bUsername ? $aQuery['username'] : '');
  $sPassword   = ($bPassword ? $aQuery['password'] : '');
  
  $mResult     = false;
  $sFocusField = "username";
  
  global $_CONFIG;
  
  if ($_CONFIG['security']['disable_user_login']) {
    $mResult = "Login has been temporarily disabled on our website.<br /><br />You can email us at "
             . "<a href=\"mailto:" . Email::$oBNETDocsRecipient->fGetAddress() . "\">" . Email::$oBNETDocsRecipient->fGetAddress() . "</a> "
             . "if you need something.";
  } else if ($bUsername && $bPassword) {
    $oUser = User::fFindUserByUsername($sUsername);
    if (!$oUser) {
      $mResult = "Unable to locate that username in our database.";
    } else if (!$oUser->fCheckPassword($sPassword)) {
      $sFocusField = "password";
      $mResult     = "Incorrect password.";
    } else if (is_null($oUser->fGetVerifiedDate())) {
      $mResult = "You have not verified your account yet. If you did not receive the email, perform a password reset on your account.";
    } else if ($oUser->fGetStatus() & User::STATUS_DISABLED_BY_SYSTEM) {
      $mResult = "Your account has been disabled automatically by our system. "
               . "Email us at <a href=\"mailto:" . Email::$oBNETDocsRecipient->fGetAddress() . "\">" . Email::$oBNETDocsRecipient->fGetAddress() . "</a> "
               . "to continue further.";
    } else if ($oUser->fGetStatus() & User::STATUS_DISABLED_BY_STAFF) {
      $mResult = "Your account has been disabled by one of our staff members. "
               . "Email us at <a href=\"mailto:" . Email::$oBNETDocsRecipient->fGetAddress() . "\">" . Email::$oBNETDocsRecipient->fGetAddress() . "</a> "
               . "to continue further.";
    } else if ($oUser->fGetStatus() & User::STATUS_DISABLED_BY_SELF) {
      $mResult = "Your account was disabled by yourself at an earlier date. "
               . "Email us at <a href=\"mailto:" . Email::$oBNETDocsRecipient->fGetAddress() . "\">" . Email::$oBNETDocsRecipient->fGetAddress() . "</a> "
               . "to continue further.";
    } else {
      $mResult = true;
      BNETDocs::$oUser = $oUser;
    }
  }
  
  ob_start('ob_gzhandler');
  if (isset($aGetQuery['ajax'])) {
    include('./includes/user/login-ajax.php');
  } else {
    include('./includes/user/login.php');
  }
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  if ($aQuery || $_CONFIG['security']['disable_user_login'])
    $oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  