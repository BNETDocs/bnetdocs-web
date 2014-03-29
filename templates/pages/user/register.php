<?php
  
  $aQuery = $oContext->fGetRequestQueryArray();
  $sVerifiedId = (isset($aQuery['id']) ? $aQuery['id'] : '');
  
  $usr = User::fFindUserByVerifiedId($sVerifiedId);
  if ($usr) {
    $usr->fSetVerifiedDate(date('Y-m-d H:i:s'));
    $stat = Email::fSendWelcome($usr);
  } else {
    $stat = false;
  }
  
  ob_start('ob_gzhandler');
  include('./includes/user/register.php');
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  //$oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'text/html;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  