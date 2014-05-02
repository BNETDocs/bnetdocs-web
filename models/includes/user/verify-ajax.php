<?php
  $sPageTitle = 'User Verify - BNETDocs';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/login_page.css', true);
  $oContext->fSetResponseHeader('X-Page-Title', $sPageTitle);
  $oContext->fSetResponseHeader('X-Page-Extra-Style', $sPageAdditionalStyle);
  $sFormClass = "";
  if (is_string($mResult)) $sFormClass = " class=\"red\"";
  else if ($mResult === true) $sFormClass = " class=\"green\"";
  echo "      <form method=\"POST\" action=\"/user/verify\"" . $sFormClass . ">\n";
  echo "        <div class=\"title\">Verify Account</div>\n";
  echo "        <div class=\"content\" id=\"verify_account_form\">\n";
  if (is_string($mResult)) {
    echo "        <p>" . $mResult . "</p>\n";
  } else if ($mResult === true && $oUser instanceof User) {
    echo "        <p>Your account has been activated and you may now <a href=\"/user/login?username=" . urlencode($oUser->fGetUsername()) . "\">log in</a>.</p>\n";
  } else if ($mResult === true) {
    echo "        <p>Your account has been activated and you may now <a href=\"/user/login\">log in</a>.</p>\n";
  } else {
    echo "        <p>Enter the verification id given to you in your email.</p>\n";
  }
  echo "          <label for=\"id\" title=\"Enter the verification id given to you in your email here.\">Verification Id:</label>\n";
  echo "          <input id=\"id\" name=\"id\" type=\"text\" tabindex=\"1\"" . ($sFocusField == "id" ? " autofocus=\"autofocus\"" : "") . " required=\"required\" title=\"Enter the verification id given to you in your email.\" value=\"" . htmlspecialchars($sId, ENT_XML1, "UTF-8") . "\" />\n";
  echo "          <input type=\"submit\" tabindex=\"2\" title=\"Click to activate your account.\" value=\"Verify Account\" />\n";
  echo "        </div>\n";
  echo "      </form>\n";
  