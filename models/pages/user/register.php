<?php
  
  $aGetQuery  = $oContext->fGetRequestQueryArray();
  $aPostQuery = $oContext->fGetRequestPostArray();
  $aQuery     = array_merge($aGetQuery, $aPostQuery);
  
  $sCSRFToken   = (isset($aQuery['csrf'])         ? $aQuery['csrf']         : '');
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
  
  global $_CONFIG;
  
  if ($_CONFIG['security']['disable_user_registration']) {
    $sUserRegisterFailed = "User registration has been temporarily disabled on our website.<br /><br />You can email us at "
                         . "<a href=\"mailto:" . Email::$oBNETDocsRecipient->fGetAddress() . "\">" . Email::$oBNETDocsRecipient->fGetAddress() . "</a> "
                         . "if you need something.";
  } else if ($sRegister) {
    if (!AntiCSRF::fCheckToken($sCSRFToken)) {
      $sUserRegisterFailed = "Cross-Site Request Forgery detected. Try registering again.";
    } else {
      $mCheckForm = User::fCheckForm($sUsername, $sDisplayName, $sPasswordOne, $sPasswordTwo, $sEmailOne, $sEmailTwo, true, true, true, true);
      if ($mCheckForm !== true) {
        if (stripos($mCheckForm, "username") !== false) {
          $sFocusField = "username";
        } else if (stripos($mCheckForm, "display name") !== false) {
          $sFocusField = "display_name";
        } else if (stripos($mCheckForm, "password") !== false) {
          $sFocusField = "password_1";
        } else if (stripos($mCheckForm, "email") !== false || stripos($mCheckForm, "e-mail") !== false) {
          $sFocusField = "email_1";
        } else {
          $sFocusField = "";
        }
        $sUserRegisterFailed = $mCheckForm;
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
            User::fGetReadACLs(true),
            date('Y-m-d H:i:s.000000'),
            null,
            User::fGenerateVerifiedId()
          );
          if (!$oUser->fSave()) {
            $sUserRegisterFailed = "Failed to put your new account into our database. Try again later.";
          } else {
            BNETDocs::$oLogger->fLogEvent('user_created', $oContext->fGetRequestIPAddress(), $oUser->fGetUId(), array());
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
  }
  
  ob_start('ob_gzhandler');
  if (isset($aGetQuery['ajax'])) {
    include('./includes/user/register-ajax.php');
  } else {
    include('./includes/user/register.php');
  }
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(($_CONFIG['security']['disable_user_registration'] ? 503 : 200));
  if ($aQuery || $_CONFIG['security']['disable_user_registration'])
    $oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  