<?php
  $sPageTitle = 'Register';
  $sPageAdditionalStyle = BnetDocs::fGetCurrentFullURL('/login_page.css', true);
  include('./includes/header.php');
    $sRegisterFormClass = "";
    if (!empty($sUserRegisterFailed)) $sRegisterFormClass = " class=\"red\"";
    else if ($sUserRegisterFailed) $sRegisterFormClass = " class=\"green\"";
    echo "      <form method=\"POST\" action=\"/user/register\"" . $sRegisterFormClass . ">\n";
    echo "        <input type=\"hidden\" name=\"submit\" value=\"1\" />\n";
    echo "        <div class=\"title\">Create Account</div>\n";
    echo "        <div class=\"content\" id=\"register_form\">\n";
    if (!empty($sUserRegisterFailed)) {
      echo "        <p>" . $sUserRegisterFailed . "</p>\n";
    } else {
      echo "        <p>All fields except for display name are required.</p>\n";
    }
    echo "          <label for=\"username\">Username:</label>\n";
    echo "          <input id=\"username\" name=\"username\" type=\"text\" tabindex=\"1\" autofocus=\"autofocus\" value=\"" . $sUsername . "\" />\n";
    echo "          <label for=\"display_name\">Display Name:</label>\n";
    echo "          <input id=\"display_name\" name=\"display_name\" type=\"text\" tabindex=\"2\" value=\"" . $sDisplayName . "\" />\n";
    echo "          <label for=\"password_1\">Password:</label>\n";
    echo "          <input id=\"password_1\" name=\"password_1\" type=\"password\" tabindex=\"3\" value=\"\" />\n";
    echo "          <label for=\"password_2\">Confirm Password:</label>\n";
    echo "          <input id=\"password_2\" name=\"password_2\" type=\"password\" tabindex=\"4\" value=\"\" />\n";
    echo "          <label for=\"email_1\">Email Address:</label>\n";
    echo "          <input id=\"email_1\" name=\"email_1\" type=\"email\" tabindex=\"5\" value=\"" . $sEmailOne . "\" />\n";
    echo "          <label for=\"email_2\">Confirm Email Address:</label>\n";
    echo "          <input id=\"email_2\" name=\"email_2\" type=\"email\" tabindex=\"6\" value=\"" . $sEmailTwo . "\" />\n";
    echo "          <input id=\"register\" type=\"submit\" tabindex=\"7\" value=\"Register\" />\n";
    echo "        </div>\n";
    echo "      </form>\n";
    echo "      <form method=\"GET\" action=\"/user/login\">\n";
    echo "        <div class=\"title\">Account Login</div>\n";
    echo "        <div class=\"content\" id=\"login_form\">\n";
    echo "          <input type=\"hidden\" name=\"username\" value=\"" . $sUsername . "\" />\n";
    echo "          <input id=\"login\" type=\"submit\" tabindex=\"8\" value=\"Go to Login Form\" />\n";
    echo "        </div>\n";
    echo "      </form>\n";
  include('./includes/footer.php');
  