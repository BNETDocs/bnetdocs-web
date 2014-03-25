<?php
  
  $sRequestMethod = $oContext->fGetRequestMethod();
  if (!in_array($sRequestMethod, array('GET', 'HEAD', 'OPTIONS'))) {
    $oContext->fSetResponseCode(405);
    $oContext->fSetResponseHeader('Allow', 'GET, HEAD, OPTIONS');
    $oContext->fSetResponseHeader('Content-Type', 'text/plain;charset=utf-8');
    $oContext->fSetResponseContent('Method not allowed: ' . $sRequestMethod);
    return;
  }
  
  $aData = array(
    'channel'       => array(
      'title'       => 'Logs - BnetDocs: Phoenix',
      'link'        => BnetDocs::fGetCurrentFullURL(),
      'description' => 'Summarized logs of activities on BNETDocs',
      'language'    => 'en-us',
      'docs'        => 'http://blogs.law.harvard.edu/tech/rss',
      'copyright'   => 'BnetDocs and its assets are property of the Battle.net community members. '.
                       'Blizzard and its other assets are copyrighted to Blizzard and/or its parent corporation Vivendi. '.
                       'Copyright infringements will be prosecuted to the fullest extent allowable by law. '.
                       'Please view our legal disclaimer and terms of service.',
    ),
  );
  
  ob_start('ob_gzhandler');
  XMLEncoder::$bAddTypeAttributes       = false;
  XMLEncoder::$sInvalidKeyAttributeName = '';
  XMLEncoder::$sInvalidKeyName          = 'item';
  echo RSSEncoder::fEncode($aData, 'rss', true);
  $sFeed = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  //$oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'application/rss+xml;charset=utf-8');
  $oContext->fSetResponseContent($sFeed);
  