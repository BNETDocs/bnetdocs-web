<?php
  
  $sRedirectURL = BnetDocs::fGetCurrentFullURL('/news');
  $sRedirectPage = ''
    ."<!DOCTYPE html>\n"
    ."<html>\n"
    ."  <head>\n"
    ."    <title>Redirect</title>\n"
    ."    <meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\">\n"
    ."    <meta http-equiv=\"Location\" content=\"$sRedirectURL\">\n"
    ."    <meta http-equiv=\"Refresh\" content=\"0;url=".urlencode($sRedirectURL)."\">\n"
    ."  </head>\n"
    ."  <body>\n"
    ."    <a href=\"".urlencode($sRedirectURL)."\">$sRedirectURL</a>\n"
    ."  </body>\n"
    ."</html>";
  
  $oContext->fSetResponseCode(302);
  //$oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'text/html;charset=utf-8');
  $oContext->fSetResponseHeader('Location', $sRedirectURL);
  $oContext->fSetResponseContent($sRedirectPage);
  