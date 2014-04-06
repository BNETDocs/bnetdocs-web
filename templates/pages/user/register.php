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
  $sRegister    = (isset($aQuery['submit'])       ? $aQuery['submit']       : '');
  
  $sUserRegisterFailed  = "";
  $bUserRegisterSuccess = false;
  $sFocusField          = "username";
  
  if ($sRegister) {
    if (empty($sUsername)) {
      $sFocusField = "username"; $sUserRegisterFailed = "Your username cannot be blank.";
    } else if (!preg_match(User::USERNAME_ALLOWED_CHARACTERS, $sUsername)) {
      $sFocusField = "username"; $sUserRegisterFailed = "Your username must not contain special characters.";
    } else if (!preg_match(User::DISPLAYNAME_ALLOWED_CHARACTERS, $sUsername)) {
      $sFocusField = "display_name"; $sUserRegisterFailed = "Your display name must not contain special characters.";
    } else if ($sPasswordOne != $sPasswordTwo) {
      $sFocusField = "password_1"; $sUserRegisterFailed = "The two passwords do not match.";
    } else if (strlen($sPasswordOne) < User::PASSWORD_LENGTH_MINIMUM) {
      $sFocusField = "password_1"; $sUserRegisterFailed = "Your password must be at least " . User::PASSWORD_LENGTH_MINIMUM . " characters.";
    } else if (strlen($sPasswordOne) > User::PASSWORD_LENGTH_MAXIMUM) {
      $sFocusField = "password_1"; $sUserRegisterFailed = "Your password must be at most " . User::PASSWORD_LENGTH_MAXIMUM . " characters.";
    } else if (User::PASSWORD_CANNOT_CONTAIN_USERNAME && stripos($sPasswordOne, $sUsername) !== false) {
      $sFocusField = "password_1"; $sUserRegisterFailed = "Your password cannot contain your username.";
    } else if (User::PASSWORD_CANNOT_CONTAIN_DISPLAYNAME && stripos($sPasswordOne, $sDisplayName) !== false) {
      $sFocusField = "password_1"; $sUserRegisterFailed = "Your password cannot contain your display name.";
    } else if (User::PASSWORD_CANNOT_CONTAIN_EMAIL && stripos($sPasswordOne, $sEmailOne) !== false) {
      $sFocusField = "password_1"; $sUserRegisterFailed = "Your password cannot contain your email address.";
    } else if (User::PASSWORD_REQUIRES_UPPERCASE_LETTERS && !preg_match('/[A-Z]/', $sPasswordOne)) {
      $sFocusField = "password_1"; $sUserRegisterFailed = "Your password must use at least one uppercase letter.";
    } else if (User::PASSWORD_REQUIRES_LOWERCASE_LETTERS && !preg_match('/[a-z]/', $sPasswordOne)) {
      $sFocusField = "password_1"; $sUserRegisterFailed = "Your password must use at least one lowercase letter.";
    } else if (User::PASSWORD_REQUIRES_NUMBERS && !preg_match('/[0-9]/', $sPasswordOne)) {
      $sFocusField = "password_1"; $sUserRegisterFailed = "Your password must use at least one numeric character.";
    } else if (User::PASSWORD_REQUIRES_SYMBOLS && !preg_match(User::PASSWORD_REQUIRES_SYMBOLS, $sPasswordOne)) {
      $sFocusField = "password_1"; $sUserRegisterFailed = "Your password must use at least one symbol.";
    } else if (strtolower($sEmailOne) != strtolower($sEmailTwo)) {
      $sFocusField = "email_1"; $sUserRegisterFailed = "The two email addresses do not match.";
    } else {
      $oUser = User::fFindUserByUsername($sUsername);
      if ($oUser) {
        $sUserRegisterFailed = "That username is already registered. Pick a unique name.";
      } else {
        $iPasswordSalt = User::fGeneratePasswordSalt();
        $sPasswordHash = User::fHashPassword($sPasswordOne, $iPasswordSalt);
        $oUser = new User(
          $sEmailOne,
          $sUsername,
          $sDisplayName,
          $sPasswordHash,
          $iPasswordSalt,
          0,
          date('Y-m-d H:i:s.000000'),
          null,
          User::fGenerateVerifiedId()
        );
        if (!$oUser->fSave()) {
          $sUserRegisterFailed = "Failed to put your new account into our database. Try again later.";
        } else {
          BNETDocs::$oUser = $oUser;
          if (!Email::fSendWelcome($oUser)) {
            $sFocusField         = "email_1";
            $sUserRegisterFailed = "Your account has been created, but we failed to send an email to your email address. You must immediately proceed with a password reset operation in order to log in.";
          } else {
            $sUserRegisterFailed  = "";
            $bUserRegisterSuccess = true;
          }
        }
      }
      
      //} else if (!Email::fSendWelcome($oUser)) {
      //  $sUserRegisterFailed = "Failed to send a welcome email to your new user. You will need to do a password reset to access your account.";
    }
  }
  
  ob_start('ob_gzhandler');
  if (isset($aGetQuery['ajax'])) {
    include('./includes/user/register-ajax.php');
  } else {
    include('./includes/user/register.php');
  }
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  if ($aQuery)
    $oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  