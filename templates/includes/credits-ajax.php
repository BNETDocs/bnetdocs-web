<?php
  $sPageTitle = 'Contributors - BNETDocs';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/contributions_page.css', true);
  $oContext->fSetResponseHeader('X-Page-Title', $sPageTitle);
  $oContext->fSetResponseHeader('X-Page-Extra-Style', $sPageAdditionalStyle);
  
  echo "        <article>\n";
  echo "          <h1>Contributors</h1>\n";
  echo "          <section>\n";
  echo "            <p>The following is a list of users with the amount of content they have added to the documentation and the website as a whole.</p>\n";
  echo "            <p><strong>There are no contributions to view at this time.</strong></p>\n";
  echo "          </section>\n";
  echo "        </article>\n";
  