<?php
  
  $aGetQuery  = $oContext->fGetRequestQueryArray();
  $aPostQuery = $oContext->fGetRequestPostArray();
  $aQuery     = array_merge($aGetQuery, $aPostQuery);
  
  $sUsername    = (isset($aQuery['username'])     ? $aQuery['username']     : '');
  $sDisplayName = (isset($aQuery['display_name']) ? $aQuery['display_name'] : '');
  $sPasswordOne = (isset($aQuery['password_1'])   ? $aQuery['password_1']   : '');
  $sPasswordTwo = (isset($aQuery['password_2'])   ? $aQuery['password_2']   : '');
  $sEmailOne    = (isset($aQuery['email_1'])      ? $aQuery['email_1']      : '');
  $sEmailTwo    = (isset($aQuery['email_2'])      ? $aQuery['email_2']      : '');
  
  if (!$aQuery) {
    $sUserRegisterFailed = "";
  } else {
    if (strtolower($sEmailOne) != strtolower($sEmailTwo)) {
      $sUserRegisterFailed = "The two email addresses do not match.";
    } else if ($sPasswordOne != $sPasswordTwo) {
      $sUserRegisterFailed = "The two passwords do not match.";
    } else if (strlen($sPasswordOne) < User::PASSWORD_LENGTH_MINIMUM) {
      $sUserRegisterFailed = "Your password must be at least " . User::PASSWORD_LENGTH_MINIMUM . " characters.";
    } else if (strlen($sPasswordOne) > User::PASSWORD_LENGTH_MAXIMUM) {
      $sUserRegisterFailed = "Your password must be at most " . User::PASSWORD_LENGTH_MAXIMUM . " characters.";
    } else if (User::PASSWORD_CANNOT_CONTAIN_USERNAME && stripos($sPasswordOne, $sUsername) !== false) {
      $sUserRegisterFailed = "Your password cannot contain your username.";
    } else if (User::PASSWORD_CANNOT_CONTAIN_DISPLAYNAME && stripos($sPasswordOne, $sDisplayName) !== false) {
      $sUserRegisterFailed = "Your password cannot contain your display name.";
    } else if (User::PASSWORD_CANNOT_CONTAIN_EMAIL && stripos($sPasswordOne, $sEmailOne) !== false) {
      $sUserRegisterFailed = "Your password cannot contain your email address.";
    } else if (User::PASSWORD_REQUIRES_UPPERCASE_LETTERS && !preg_match('/[A-Z]/', $sPasswordOne)) {
      $sUserRegisterFailed = "Your password must use at least one uppercase letter.";
    } else if (User::PASSWORD_REQUIRES_LOWERCASE_LETTERS && !preg_match('/[a-z]/', $sPasswordOne)) {
      $sUserRegisterFailed = "Your password must use at least one lowercase letter.";
    } else if (User::PASSWORD_REQUIRES_NUMBERS && !preg_match('/[0-9]/', $sPasswordOne)) {
      $sUserRegisterFailed = "Your password must use at least one numeric character.";
    } else if (User::PASSWORD_REQUIRES_SYMBOLS && !preg_match("/['\":;^£$%&*()}{\\[\\]@#~\\?><>,.\\/|=_+¬\\-]/", $sPasswordOne)) {
      $sUserRegisterFailed = "Your password must use at least one symbol.";
    } else {
      
      $oUser = User::fFindUserByUsername($sUsername);
      
      if ($oUser) {
        $sUserRegisterFailed = "That username is already registered. Pick a unique name.";
      } else {
        $sUserRegisterFailed = "";
      }
      
      //} else if (!Email::fSendWelcome($oUser)) {
      //  $sUserRegisterFailed = "Failed to send a welcome email to your new user. You will need to do a password reset to access your account.";
    }
  }
  
  ob_start('ob_gzhandler');
  include('./includes/user/register.php');
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  if ($aQuery)
    $oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  