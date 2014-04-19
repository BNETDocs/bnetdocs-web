<?php
  $sPageTitle = 'Register - BNETDocs';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/login_page.css', true);
  $oContext->fSetResponseHeader('X-Page-Title', $sPageTitle);
  $oContext->fSetResponseHeader('X-Page-Extra-Style', $sPageAdditionalStyle);
  if ($_CONFIG['security']['disable_user_registration']) {
    echo "      <form class=\"red\">\n";
    echo "        <div class=\"title\">Create Account</div>\n";
    echo "        <div class=\"content\" id=\"register_form\">\n";
    echo "          <p style=\"margin-bottom:0px;\">" . $sUserRegisterFailed . "</p>\n";
    echo "        </div>\n";
    echo "      </form>\n";
  } else {
    $sRegisterFormClass = "";
    if (!empty($sUserRegisterFailed)) $sRegisterFormClass = " class=\"red\"";
    else if ($bUserRegisterSuccess) $sRegisterFormClass = " class=\"green\"";
    echo "      <form method=\"POST\" action=\"/user/register\"" . $sRegisterFormClass . ">\n";
    echo "        <input type=\"hidden\" name=\"submit\" value=\"1\" />\n";
    echo "        <div class=\"title\">Create Account</div>\n";
    echo "        <div class=\"content\" id=\"register_form\">\n";
    if (!empty($sUserRegisterFailed)) {
      echo "        <p>" . $sUserRegisterFailed . "</p>\n";
    } else if ($bUserRegisterSuccess) {
      echo "        <p>Your account has been created. Verify your new account by opening the link in the email that was sent your email address.</p>\n";
    } else {
      echo "        <p>All fields except for display name are required.</p>\n";
    }
    echo "          <input type=\"hidden\" name=\"csrf\" value=\"" . htmlspecialchars(AntiCSRF::fGetToken(), ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <label for=\"username\" title=\"Enter your username for the new account.\">Username:</label>\n";
    echo "          <input id=\"username\" name=\"username\" type=\"text\" tabindex=\"1\" ".($sFocusField=="username"?"autofocus=\"autofocus\" ":"")."required=\"required\" maxlength=\"31\" title=\"Enter your username for the new account.\" value=\"" . htmlspecialchars($sUsername, ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <label for=\"display_name\" title=\"Optionally, enter a display name for the new account.\">Display Name:</label>\n";
    echo "          <input id=\"display_name\" name=\"display_name\" type=\"text\" tabindex=\"2\" ".($sFocusField=="display_name"?"autofocus=\"autofocus\" ":"")."maxlength=\"63\" title=\"Optionally, enter a display name for the new account.\" value=\"" . htmlspecialchars($sDisplayName, ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <label for=\"password_1\" title=\"Enter the password you wish to use for the new account.\">Password:</label>\n";
    echo "          <input id=\"password_1\" name=\"password_1\" type=\"password\" tabindex=\"3\" ".($sFocusField=="password_1"?"autofocus=\"autofocus\" ":"")."required=\"required\" maxlength=\"" . User::PASSWORD_LENGTH_MAXIMUM . "\" title=\"Enter the password you wish to use for the new account.\" value=\"\" />\n";
    echo "          <label for=\"password_2\" title=\"Please re-enter the password to confirm you didn't make any mistakes.\">Confirm Password:</label>\n";
    echo "          <input id=\"password_2\" name=\"password_2\" type=\"password\" tabindex=\"4\" ".($sFocusField=="password_2"?"autofocus=\"autofocus\" ":"")."required=\"required\" maxlength=\"" . User::PASSWORD_LENGTH_MAXIMUM . "\" autocomplete=\"off\" title=\"Please re-enter the password to confirm you didn't make any mistakes.\" value=\"\" />\n";
    echo "          <label for=\"email_1\" title=\"Enter the email address you wish to use for the new account. An activation link will be sent to this address.\">Email Address:</label>\n";
    echo "          <input id=\"email_1\" name=\"email_1\" type=\"email\" tabindex=\"5\" ".($sFocusField=="email_1"?"autofocus=\"autofocus\" ":"")."required=\"required\" maxlength=\"127\" title=\"Enter the email address you wish to use for the new account. An activation link will be sent to this address.\" value=\"" . htmlspecialchars($sEmailOne, ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <label for=\"email_2\" title=\"Please re-enter the email address to confirm you didn't make any mistakes.\">Confirm Email Address:</label>\n";
    echo "          <input id=\"email_2\" name=\"email_2\" type=\"email\" tabindex=\"6\" ".($sFocusField=="email_2"?"autofocus=\"autofocus\" ":"")."required=\"required\" maxlength=\"127\" autocomplete=\"off\" title=\"Please re-enter the email address to confirm you didn't make any mistakes.\" value=\"" . htmlspecialchars($sEmailTwo, ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <input id=\"register\" type=\"submit\" tabindex=\"7\" title=\"Click to create your new account.\" value=\"Register\" />\n";
    echo "        </div>\n";
    echo "      </form>\n";
    echo "      <form method=\"GET\" action=\"/user/login\">\n";
    echo "        <div class=\"title\">Account Login</div>\n";
    echo "        <div class=\"content\" id=\"login_form\">\n";
    echo "          <input type=\"hidden\" name=\"username\" value=\"" . htmlspecialchars($sUsername, ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <input id=\"login\" type=\"submit\" tabindex=\"8\" title=\"Click to be taken to the account login form.\" value=\"Go to Login Form\" />\n";
    echo "        </div>\n";
    echo "      </form>\n";
  }
  