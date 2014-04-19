<?php echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>"; ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us">
  <head>
    <title><?php if (isset($sPageTitle) && !empty($sPageTitle)) echo $sPageTitle . ' - '; ?>BNETDocs</title>
<?php if (isset($sPageAdditionalStyle) && !empty($sPageAdditionalStyle)) {
?>    <link rel="stylesheet" href="<?php echo $sPageAdditionalStyle; ?>" type="text/css" media="all" />
<?php }
?>    <link rel="stylesheet" href="<?php echo BNETDocs::fGetCurrentFullURL('/main.css', true); ?>" type="text/css" media="all" />
    <script type="text/javascript" src="<?php echo BNETDocs::fGetCurrentFullURL('/BNETDocs.js', true); ?>"><![CDATA[]]></script>
    <link rel="icon" href="<?php echo BNETDocs::fGetCurrentFullURL('/favicon.png', true); ?>" type="image/png" sizes="32x32" />
    <link rel="icon" href="<?php echo BNETDocs::fGetCurrentFullURL('/opera-icon.png', true); ?>" type="image/png" sizes="256x160" />
    <link rel="alternate" href="<?php echo BNETDocs::fGetCurrentFullURL('/rss/news', true); ?>" type="application/rss+xml" title="BNETDocs News" />
    <link rel="license" href="<?php echo BNETDocs::fGetCurrentFullURL('/legal', true); ?>" />
    <meta name="description" content="Battle.net logon sequences, packets, information, and protocols reference site." />
    <meta name="keywords" content="battle.net, starcraft, warcraft, diablo, blizzard, logon sequences, packets, information, protocols, reference, programming, coding" />
<?php
    foreach ($aOpenGraphItems as $sKey => $sVal) { ?>
    <meta property="<?php echo $sKey; ?>" content="<?php echo $sVal; ?>" />
<?php } ?>  </head>
  <body>
    <div id="header">
      Battle.net Documentation
    </div>
    <div id="container">
      <div class="sidebar" id="sidebar_left">
        <div>Portal</div>
        <a href="<?php echo BNETDocs::fGetCurrentFullURL('/', true); ?>">Home</a>
        <a href="<?php echo BNETDocs::fGetCurrentFullURL('/news', true); ?>">News</a>
        <a href="<?php echo BNETDocs::fGetCurrentFullURL('/credits', true); ?>">Contributors</a>
        <a href="<?php echo BNETDocs::fGetCurrentFullURL('/legal', true); ?>">Terms of Service</a>
        <div>Account Management</div>
        <a href="<?php echo BNETDocs::fGetCurrentFullURL('/user/login', true); ?>">Account Login</a>
        <a href="<?php echo BNETDocs::fGetCurrentFullURL('/user/register', true); ?>">Create Account</a>
        <div>Documentation</div>
        <a href="<?php echo BNETDocs::fGetCurrentFullURL('/document/search', true); ?>">Search Documents</a>
        <a href="<?php echo BNETDocs::fGetCurrentFullURL('/packet/search', true); ?>">Search Packets</a>
        <a href="<?php echo BNETDocs::fGetCurrentFullURL('/document/popular', true); ?>">View Popular Documents</a>
        <a href="<?php echo BNETDocs::fGetCurrentFullURL('/packet/popular', true); ?>">View Popular Packets</a>
<?php if (!is_null(BNETDocs::$oUserSession->fGetUserObject()) && (BNETDocs::$oUserSession->fGetUserObject()->fHasWriteACLs())) {
?>        <div>Administration</div>
<?php   if (BNETDocs::$oUserSession->fGetUserObject()->fGetStatus() & User::STATUS_ACL_DOCUMENTS_WRITE) {
?>        <a href="<?php echo BNETDocs::fGetCurrentFullURL('/document/create', true); ?>">Create Document</a>
<?php   }
        if (BNETDocs::$oUserSession->fGetUserObject()->fGetStatus() & User::STATUS_ACL_NEWS_WRITE) {
?>        <a href="<?php echo BNETDocs::fGetCurrentFullURL('/news/create', true); ?>">Create News Post</a>
<?php   }
        if (BNETDocs::$oUserSession->fGetUserObject()->fGetStatus() & User::STATUS_ACL_PACKETS_WRITE) {
?>        <a href="<?php echo BNETDocs::fGetCurrentFullURL('/packet/create', true); ?>">Create Packet</a>
<?php   }
      }
?>      </div>
      <div id="content">