<?php
  $sPageTitle = 'Password Reset - BNETDocs';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/login_page.css', true);
  $oContext->fSetResponseHeader('X-Page-Title', $sPageTitle);
  $oContext->fSetResponseHeader('X-Page-Extra-Style', $sPageAdditionalStyle);
  $sFormClass = "";
  if (is_string($mResult)) $sFormClass = " class=\"red\"";
  else if ($mResult === true) $sFormClass = " class=\"green\"";
  echo "      <form method=\"POST\" action=\"/user/password_reset\"" . $sFormClass . ">\n";
  echo "        <div class=\"title\">Password Reset</div>\n";
  echo "        <div class=\"content\" id=\"password_reset_form\">\n";
  if ($bId) {
    if (is_string($mResult)) {
      echo "        <p>" . $mResult . "</p>\n";
    } else if ($mResult === true) {
      echo "        <p>Your account has been activated, your password has been changed, and you may now <a href=\"/user/login?username=" . urlencode($oUser->fGetUsername()) . "\">log in</a>.</p>\n";
    } else {
      echo "        <p>Enter the verification id given to you in your email, followed by your new password.</p>\n";
    }
    echo "          <input type=\"hidden\" name=\"csrf\" value=\"" . htmlspecialchars(AntiCSRF::fGetToken(), ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <label for=\"id\" title=\"Enter the verification id given to you in your email here.\">Verification Id:</label>\n";
    echo "          <input id=\"id\" name=\"id\" type=\"text\" tabindex=\"1\"" . ($sFocusField == "id" ? " autofocus=\"autofocus\"" : "") . " required=\"required\" title=\"Enter the verification id given to you in your email.\" value=\"" . htmlspecialchars($sId, ENT_XML1, "UTF-8") . "\" />\n";
    if ($oUser) {
      echo "          <label for=\"username\">Username:</label>\n";
      echo "          <input id=\"username\" type=\"text\" tabindex=\"2\"" . ($sFocusField == "username" ? " autofocus=\"autofocus\"" : "") . " disabled=\"disabled\" value=\"" . htmlspecialchars($oUser->fGetUsername(), ENT_XML1, "UTF-8") . "\" />\n";
      echo "          <label for=\"password_1\" title=\"Enter your new password here.\">New Password:</label>\n";
      echo "          <input id=\"password_1\" name=\"password_1\" type=\"password\" tabindex=\"3\"" . ($sFocusField == "password_1" ? " autofocus=\"autofocus\"" : "") . " required=\"required\" title=\"Enter your new password here.\" value=\"\" />\n";
      echo "          <label for=\"password_2\" title=\"Repeat your new password here.\">Confirm Password:</label>\n";
      echo "          <input id=\"password_2\" name=\"password_2\" type=\"password\" tabindex=\"4\"" . ($sFocusField == "password_2" ? " autofocus=\"autofocus\"" : "") . " required=\"required\" title=\"Enter your new password here.\" value=\"\" />\n";
    }
    echo "          <input type=\"submit\" tabindex=\"5\" title=\"Click to change your account password.\" value=\"Reset Password\" />\n";
  } else {
    if (is_string($mResult)) {
      echo "        <p>" . $mResult . "</p>\n";
    } else if ($mResult === true) {
      echo "        <p>An email has been sent to the email address given on the account.</p>\n";
    } else {
      echo "        <p>Enter the username you used when you created the account.</p>\n";
    }
    echo "          <input type=\"hidden\" name=\"csrf\" value=\"" . htmlspecialchars(AntiCSRF::fGetToken(), ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <label for=\"username\" title=\"Enter the username you used when you created the account.\">Username:</label>\n";
    echo "          <input id=\"username\" name=\"username\" type=\"text\" tabindex=\"1\" autofocus=\"autofocus\" required=\"required\" title=\"Enter the username you used when you created the account.\" value=\"" . htmlspecialchars($sUsername, ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <input type=\"submit\" tabindex=\"2\" title=\"Click to send an email to the email address on the account.\" value=\"Reset Password\" />\n";
  }
  echo "        </div>\n";
  echo "      </form>\n";
  