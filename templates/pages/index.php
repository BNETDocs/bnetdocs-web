<?php
  
  $sQueryString = $oContext->fGetRequestQueryString();
  if (!empty($sQueryString)) $sQueryString = '?' . $sQueryString;
  
  $sRedirectURL = BnetDocs::fGetCurrentFullURL('/news' . $sQueryString);
  $sSafeRedirectURL = urlencode($sRedirectURL);
  
  ob_start('ob_gzhandler');
  include_once('./includes/redirect.php');
  $sRedirectPage = ob_get_clean();
  
  $oContext->fSetResponseCode(302);
  $oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'text/html;charset=utf-8');
  $oContext->fSetResponseHeader('Location', $sRedirectURL);
  $oContext->fSetResponseContent($sRedirectPage);
  