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
      echo "        <p>You account has been activated and you may now log in.</p>\n";
    } else {
      echo "        <p>Enter the verification id given to you in your email.</p>\n";
    }
    echo "          <label for=\"id\" title=\"Enter the verification id given to you in your email.\">Verification Id:</label>\n";
    echo "          <input id=\"id\" name=\"id\" type=\"text\" tabindex=\"1\" autofocus=\"autofocus\" required=\"required\" title=\"Enter the verification id given to you in your email.\" value=\"" . htmlspecialchars($sId, ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <input type=\"submit\" tabindex=\"2\" title=\"Click to validate your account.\" value=\"Validate\" />\n";
  } else {
    if (is_string($mResult)) {
      echo "        <p>" . $mResult . "</p>\n";
    } else if ($mResult === true) {
      echo "        <p>An email has been sent to the email address given on the account.</p>\n";
    } else {
      echo "        <p>Enter the username you used when you created the account.</p>\n";
    }
    echo "          <label for=\"username\" title=\"Enter the username you used when you created the account.\">Username:</label>\n";
    echo "          <input id=\"username\" name=\"username\" type=\"text\" tabindex=\"1\" autofocus=\"autofocus\" required=\"required\" title=\"Enter the username you used when you created the account.\" value=\"" . htmlspecialchars($sUsername, ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <input type=\"submit\" tabindex=\"2\" title=\"Click to send an email to the email address on the account.\" value=\"Reset Password\" />\n";
  }
  echo "        </div>\n";
  echo "      </form>\n";
  