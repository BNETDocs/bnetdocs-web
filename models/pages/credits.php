<?php
  
  $aGetQuery = $oContext->fGetRequestQueryArray();
  
  $aTopDocumentContributors = Contributors::fGetTopDocumentAuthors();
  $aTopPacketContributors   = Contributors::fGetTopPacketAuthors();
  $aTopServerContributors   = Contributors::fGetTopServerOwners();
  
  ob_start('ob_gzhandler');
  if (isset($aGetQuery['ajax'])) {
    include('./includes/credits-ajax.php');
  } else {
    include('./includes/credits.php');
  }
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  //$oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  