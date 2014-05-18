<?php
  $sPageTitle = 'Edit Profile - BNETDocs';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/login_page.css', true);
  $oContext->fSetResponseHeader('X-Page-Title', $sPageTitle);
  $oContext->fSetResponseHeader('X-Page-Extra-Style', $sPageAdditionalStyle);
  
  echo "      <form method=\"POST\" action=\"/user/profile/edit\">\n";
  echo "        <div class=\"title\">Edit Profile</div>\n";
  echo "        <div class=\"content\">\n";
  echo "          <p>You can edit your user profile details using this form.</p>\n";
  echo "          <input type=\"hidden\" name=\"csrf\" value=\"" . htmlspecialchars(AntiCSRF::fGetToken(), ENT_XML1, "UTF-8") . "\" />\n";
  echo "          <label for=\"display_name\" title=\"Enter an alternate name than your username you wish to use.\">Display Name:</label>\n";
  echo "          <input id=\"display_name\" name=\"display_name\" type=\"text\" tabindex=\"1\"" . ($sFocusField == "display_name" ? " autofocus=\"autofocus\"" : "") . " required=\"required\" title=\"Enter an alternate name than your username you wish to use.\" value=\"" . htmlspecialchars($sDisplayName, ENT_XML1, "UTF-8") . "\" />\n";
  echo "        </div>\n";
  echo "      </form>\n";