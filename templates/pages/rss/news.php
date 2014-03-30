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
      'category'    => 'IT/Internet/Development',
      'copyright'   => 'BnetDocs and its assets are property of the Battle.net community members. '.
                       'Blizzard and its other assets are copyrighted to Blizzard and/or its parent corporation Vivendi. '.
                       'Copyright infringements will be prosecuted to the fullest extent allowable by law. '.
                       'Please view our legal disclaimer and terms of service.',
      'description' => 'A summary of news articles on BNETDocs',
      'docs'        => 'http://blogs.law.harvard.edu/tech/rss',
      'link'        => BnetDocs::fGetCurrentFullURL(),
      'language'    => 'en-us',
      'title'       => 'BNETDocs News',
    ),
  );
  
  $oResult = false;
  $aNews   = array();
  
  $oResult = BnetDocs::$oDB->fQuery('SELECT '
    . 'n.id AS `id`,'
    . 'IFNULL(u.display_name, u.username) AS `creator`,'
    . 'IFNULL(n.edit_date, n.post_date) AS `pub_date`,'
    . 'n.title AS `title`,'
    . 'n.content AS `content` '
    . 'FROM news_posts n '
    . 'LEFT JOIN users u '
    . 'ON n.creator_uid = u.uid '
    . 'ORDER BY n.post_date DESC, n.id DESC '
    . 'LIMIT 30;');
  
  if ($oResult && $oResult instanceof MySQLResult) {
    while ($aRow = $oResult->fFetchAssoc()) {
      $aNews[] = $aRow;
    }
  } else {
    $aNews[] = array(
      'id'         => 0,
      'creator'    => 'n/a',
      'post_date'  => date('Y-m-d H:i:s T'),
      'edit_count' => 0,
      'edit_date'  => null,
      'title'      => 'ERROR RETRIEVING NEWS',
      'content'    => 'An error has occurred while retrieving the news.',
    );
  }
  
  $i = 0;
  foreach ($aNews as $aNewsItem) {
    ++$i;
    $sPermalink = BnetDocs::fGetCurrentFullURL('/news/' . urlencode($aNewsItem['id']));
    $aData['channel'][$i] = array(
      'author'      => 'no-reply@bnetdocs.org (' . $aNewsItem['creator'] . ')',
      'category'    => 'News',
      'description' => $aNewsItem['content'],
      'comments'    => $sPermalink,
      'guid'        => $sPermalink,
      'link'        => $sPermalink,
      'pubDate'     => date('D, d M Y H:i:s O', strtotime($aNewsItem['pub_date'])),
      'title'       => $aNewsItem['title'],
    );
  }
  
  ob_start('ob_gzhandler');
  XMLEncoder::$bAddTypeAttributes       = false;
  XMLEncoder::$sInvalidKeyAttributeName = '';
  XMLEncoder::$sInvalidKeyName          = 'item';
  echo RSSEncoder::fEncode($aData, 'rss', true);
  $sFeed = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  //$oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
  $oContext->fSetResponseContent($sFeed);
  