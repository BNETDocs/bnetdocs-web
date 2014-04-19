<?php
  $sPageTitle = 'Logout - BNETDocs';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/login_page.css', true);
  $oContext->fSetResponseHeader('X-Page-Title', $sPageTitle);
  $oContext->fSetResponseHeader('X-Page-Extra-Style', $sPageAdditionalStyle);
  $sFormClass = "";
  if (is_string($mResult)) $sFormClass = " class=\"red\"";
  else if ($mResult === true) $sFormClass = " class=\"green\"";
  echo "      <form method=\"POST\" action=\"/user/logout\"" . $sFormClass . ">\n";
  echo "        <div class=\"title\">Account Logout</div>\n";
  echo "        <div class=\"content\" id=\"login_form\">\n";
  if (is_string($mResult)) {
    echo "        <p>" . $mResult . "</p>\n";
  } else if ($mResult === true) {
    echo "        <p>You have successfully logged out of your account.  <a href=\"/user/login\">Click here</a> if you want to log in again.</p>\n";
  } else {
    echo "        <p>If you really want to log out, click the button below.</p>\n";
  }
  if (BNETDocs::$oUserSession && !is_null(BNETDocs::$oUserSession->fGetUserObject())) {
    echo "          <input type=\"hidden\" name=\"csrf\" value=\"" . htmlspecialchars(AntiCSRF::fGetToken(), ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <input name=\"logout\" type=\"submit\" tabindex=\"1\" title=\"Click to log out of your account.\" value=\"Log Out\" />\n";
  }
  echo "        </div>\n";
  echo "      </form>\n";
  