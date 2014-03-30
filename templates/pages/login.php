<?php
  
  $aQuery = $oContext->fGetRequestQueryArray();
  $sUsername = (isset($aQuery['username']) ? $aQuery['username'] : '');
  $sPassword = (isset($aQuery['password']) ? $aQuery['password'] : '');
  
  ob_start('ob_gzhandler');
  include('./includes/login.php');
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  //$oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  