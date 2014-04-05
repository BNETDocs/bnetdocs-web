<?php
  $sPageTitle = 'Login';
  $sPageAdditionalStyle = BnetDocs::fGetCurrentFullURL('/login_page.css', true);
  include('./includes/header.php');
    $sLoginFormClass = "";
    if (!empty($sUserLoginFailed)) $sLoginFormClass = " class=\"red\"";
    else if ($bUserLoginSuccess) $sLoginFormClass = " class=\"green\"";
    echo "      <form method=\"POST\" action=\"/user/login\"" . $sLoginFormClass . ">\n";
    echo "        <input type=\"hidden\" name=\"mode\" value=\"login\" />\n";
    echo "        <div class=\"title\">Account Login</div>\n";
    echo "        <div class=\"content\" id=\"login_form\">\n";
    if (!empty($sUserLoginFailed)) {
      echo "        <p>" . $sUserLoginFailed . "</p>\n";
    } else {
      echo "        <p>Submit your username and password below to log in.</p>\n";
    }
    echo "          <label for=\"username\">Username:</label>\n";
    echo "          <input id=\"username\" name=\"username\" type=\"text\" tabindex=\"1\"" . (empty($sPasswordResetFailed) && !$bPasswordResetSuccess ? " autofocus=\"autofocus\"" : "") . " value=\"" . $sUsername . "\" />\n";
    echo "          <label for=\"password\">Password:</label>\n";
    echo "          <input id=\"password\" name=\"password\" type=\"password\" tabindex=\"2\" value=\"\" />\n";
    echo "          <input id=\"login\" type=\"submit\" tabindex=\"3\" value=\"Log In\" />\n";
    echo "        </div>\n";
    echo "      </form>\n";
    $sPasswordResetFormClass = "";
    if (!empty($sPasswordResetFailed)) $sPasswordResetFormClass = " class=\"red\"";
    else if ($bPasswordResetSuccess) $sPasswordResetFormClass = " class=\"green\"";
    echo "      <form method=\"POST\" action=\"/user/login\"" . $sPasswordResetFormClass . ">\n";
    echo "        <input type=\"hidden\" name=\"mode\" value=\"reset_password\" />\n";
    echo "        <div class=\"title\">Reset Password</div>\n";
    echo "        <div class=\"content\" id=\"reset_password_form\">\n";
    if (!empty($sPasswordResetFailed)) {
      echo "        <p>" . $sPasswordResetFailed . "</p>\n";
    } else if ($bPasswordResetSuccess) {
      echo "        <p>An email has been sent to the address given on the account.</p>\n";
    } else {
      echo "        <p>Submit your username below and an email will be sent.</p>\n";
    }
    echo "          <label for=\"username_2\">Username:</label>\n";
    echo "          <input id=\"username_2\" name=\"username\" type=\"text\" tabindex=\"4\"" . (!empty($sPasswordResetFailed) || $bPasswordResetSuccess ? " autofocus=\"autofocus\"" : "") . " value=\"" . $sUsername . "\" />\n";
    echo "          <input id=\"reset_password\" type=\"submit\" tabindex=\"5\" value=\"Reset Password\" />\n";
    echo "        </div>\n";
    echo "      </form>\n";
    echo "      <form method=\"GET\" action=\"/user/register\">\n";
    echo "        <div class=\"title\">Create Account</div>\n";
    echo "        <div class=\"content\" id=\"register_form\">\n";
    echo "          <input type=\"hidden\" name=\"username\" value=\"" . $sUsername . "\" />\n";
    echo "          <input id=\"register\" type=\"submit\" tabindex=\"6\" value=\"Go to Registration Form\" />\n";
    echo "        </div>\n";
    echo "      </form>\n";
  include('./includes/footer.php');
  