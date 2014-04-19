<?php
  $sPageTitle = 'Login - BNETDocs';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/login_page.css', true);
  $oContext->fSetResponseHeader('X-Page-Title', $sPageTitle);
  $oContext->fSetResponseHeader('X-Page-Extra-Style', $sPageAdditionalStyle);
  if ($_CONFIG['security']['disable_user_login']) {
    echo "      <form class=\"red\">\n";
    echo "        <div class=\"title\">Account Login</div>\n";
    echo "        <div class=\"content\" id=\"login_form\">\n";
    echo "          <p style=\"margin-bottom:0px;\">" . $mResult . "</p>\n";
    echo "        </div>\n";
    echo "      </form>\n";
  } else {
    $sFormClass = "";
    if (is_string($mResult)) $sFormClass = " class=\"red\"";
    else if ($mResult === true) $sFormClass = " class=\"green\"";
    echo "      <form method=\"POST\" action=\"/user/login\"" . $sFormClass . ">\n";
    echo "        <div class=\"title\">Account Login</div>\n";
    echo "        <div class=\"content\" id=\"login_form\">\n";
    if (is_string($mResult)) {
      echo "        <p>" . $mResult . "</p>\n";
    } else if ($mResult === true) {
      echo "        <p>You have successfully logged in to your account.</p>\n";
    } else {
      echo "        <p>Submit your username and password below to log in.</p>\n";
    }
    echo "          <input type=\"hidden\" name=\"csrf\" value=\"" . htmlspecialchars(AntiCSRF::fGetToken(), ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <label for=\"username\" title=\"Enter the username you used when you created the account.\">Username:</label>\n";
    echo "          <input id=\"username\" name=\"username\" type=\"text\" tabindex=\"1\"" . ($sFocusField == "username" ? " autofocus=\"autofocus\"" : "") . " required=\"required\" title=\"Enter the username you used when you created the account.\" value=\"" . htmlspecialchars($sUsername, ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <label for=\"password\" title=\"Enter the password you use with the account.\">Password:</label>\n";
    echo "          <input id=\"password\" name=\"password\" type=\"password\" tabindex=\"2\"" . ($sFocusField == "password" ? " autofocus=\"autofocus\"" : "") . " title=\"Enter the password you use with the account.\" value=\"\" />\n";
    echo "          <input id=\"login\" type=\"submit\" tabindex=\"3\" title=\"Click to log in to your account.\" value=\"Log In\" />\n";
    echo "        </div>\n";
    echo "      </form>\n";
    echo "      <form method=\"GET\" action=\"/user/password_reset\">\n";
    echo "        <div class=\"title\">Reset Password</div>\n";
    echo "        <div class=\"content\" id=\"reset_password_form\">\n";
    if ($bUsername) {
      echo "          <input type=\"hidden\" name=\"username\" value=\"" . htmlspecialchars($sUsername, ENT_XML1, "UTF-8") . "\" />\n";
    }
    echo "          <input type=\"submit\" tabindex=\"4\" title=\"Click to be taken to the reset password form.\" value=\"Go to Reset Password Form\" />\n";
    echo "        </div>\n";
    echo "      </form>\n";
    echo "      <form method=\"GET\" action=\"/user/register\">\n";
    echo "        <div class=\"title\">Create Account</div>\n";
    echo "        <div class=\"content\" id=\"register_form\">\n";
    if ($bUsername) {
      echo "          <input type=\"hidden\" name=\"username\" value=\"" . htmlspecialchars($sUsername, ENT_XML1, "UTF-8") . "\" />\n";
    }
    echo "          <input type=\"submit\" tabindex=\"5\" title=\"Click to be taken to the account registration form.\" value=\"Go to Registration Form\" />\n";
    echo "        </div>\n";
    echo "      </form>\n";
  }
  