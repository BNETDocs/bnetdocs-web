<?php
  $sPageTitle = 'Contributors - BNETDocs';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/contributions_page.css', true);
  $oContext->fSetResponseHeader('X-Page-Title', $sPageTitle);
  $oContext->fSetResponseHeader('X-Page-Extra-Style', $sPageAdditionalStyle);
  
  echo "        <article>\n";
  echo "          <h1>Contributors</h1>\n";
  echo "          <section>\n";
  echo "            <p>The following are lists of users with the amount of content they have added to the documentation and the website as a whole.</p>\n";
  echo "          </section>\n";
  echo "        </article>\n";
  echo "        <article class=\"statistic\">\n";
  echo "          <h1>Top Document Contributors</h1>\n";
  echo "          <section>\n";
  echo "            <p>These users have added the most documents to the website.</p>\n";
  if (!$aTopDocumentContributors) {
    echo "            <p class=\"none\">There have been no contributors at this time.</p>\n";  
  } else {
    echo "            <ol>\n";
    foreach ($aTopDocumentContributors as $aContributor) {
      $sUsername    = "";
      $sDisplayName = "Anonymous";
      if ($aContributor[1] instanceof User) {
        $sUsername    = $aContributor[1]->fGetUsername();
        $sDisplayName = $aContributor[1]->fGetDisplayName();
      }
      $sName      = ContentFilter::fFilterHTML((empty($sDisplayName) ? $sUsername : $sDisplayName));
      $sDocuments = number_format($aContributor[0]) . " document" . ($aContributor[0] != 1 ? "s" : "");
      echo "              <li><strong>" . $sName . "</strong> with <strong>" . $sDocuments . "</strong>.</li>\n";
    }
    echo "            </ol>\n";
  }
  echo "          </section>\n";
  echo "        </article>\n";
  echo "        <article class=\"statistic\">\n";
  echo "          <h1>Top News Contributors</h1>\n";
  echo "          <section>\n";
  echo "            <p>These users have added the most news to the website.</p>\n";
  if (!$aTopNewsContributors) {
    echo "            <p class=\"none\">There have been no contributors at this time.</p>\n";  
  } else {
    echo "            <ol>\n";
    foreach ($aTopNewsContributors as $aContributor) {
      $sUsername    = "";
      $sDisplayName = "Anonymous";
      if ($aContributor[1] instanceof User) {
        $sUsername    = $aContributor[1]->fGetUsername();
        $sDisplayName = $aContributor[1]->fGetDisplayName();
      }
      $sName      = ContentFilter::fFilterHTML((empty($sDisplayName) ? $sUsername : $sDisplayName));
      $sNewsPosts = number_format($aContributor[0]) . " news post" . ($aContributor[0] != 1 ? "s" : "");
      echo "              <li><strong>" . $sName . "</strong> with <strong>" . $sNewsPosts . "</strong>.</li>\n";
    }
    echo "            </ol>\n";
  }
  echo "          </section>\n";
  echo "        </article>\n";
  echo "        <article class=\"statistic\">\n";
  echo "          <h1>Top Packet Contributors</h1>\n";
  echo "          <section>\n";
  echo "            <p>These users have added the most packets to the website.</p>\n";
  if (!$aTopPacketContributors) {
    echo "            <p class=\"none\">There have been no contributors at this time.</p>\n";  
  } else {
    echo "            <ol>\n";
    foreach ($aTopPacketContributors as $aContributor) {
      $sUsername    = "";
      $sDisplayName = "Anonymous";
      if ($aContributor[1] instanceof User) {
        $sUsername    = $aContributor[1]->fGetUsername();
        $sDisplayName = $aContributor[1]->fGetDisplayName();
      }
      $sName    = ContentFilter::fFilterHTML((empty($sDisplayName) ? $sUsername : $sDisplayName));
      $sPackets = number_format($aContributor[0]) . " packet" . ($aContributor[0] != 1 ? "s" : "");
      echo "              <li><strong>" . $sName . "</strong> with <strong>" . $sPackets . "</strong>.</li>\n";
    }
    echo "            </ol>\n";
  }
  echo "          </section>\n";
  echo "        </article>\n";
  echo "        <article class=\"statistic\">\n";
  echo "          <h1>Top Server Contributors</h1>\n";
  echo "          <section>\n";
  echo "            <p>These users have added the most servers to the website.</p>\n";
  if (!$aTopServerContributors) {
    echo "            <p class=\"none\">There have been no contributors at this time.</p>\n";  
  } else {
    echo "            <ol>\n";
    foreach ($aTopServerContributors as $aContributor) {
      $sUsername    = "";
      $sDisplayName = "Anonymous";
      if ($aContributor[1] instanceof User) {
        $sUsername    = $aContributor[1]->fGetUsername();
        $sDisplayName = $aContributor[1]->fGetDisplayName();
      }
      $sName    = ContentFilter::fFilterHTML((empty($sDisplayName) ? $sUsername : $sDisplayName));
      $sServers = number_format($aContributor[0]) . " server" . ($aContributor[0] != 1 ? "s" : "");
      echo "              <li><strong>" . $sName . "</strong> with <strong>" . $sServers . "</strong>.</li>\n";
    }
    echo "            </ol>\n";
  }
  echo "          </section>\n";
  echo "        </article>\n";
  