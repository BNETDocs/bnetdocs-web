<?php
  $sPageTitle = 'Password Reset - BNETDocs';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/login_page.css', true);
  $oContext->fSetResponseHeader('X-Page-Title', $sPageTitle);
  $oContext->fSetResponseHeader('X-Page-Extra-Style', $sPageAdditionalStyle);
    $sPasswordResetFormClass = "";
    if (!empty($sPasswordResetFailed)) $sPasswordResetFormClass = " class=\"red\"";
    else if ($bPasswordResetSuccess) $sPasswordResetFormClass = " class=\"green\"";
    echo "      <form method=\"POST\" action=\"/user/password_reset\"" . $sPasswordResetFormClass . ">\n";
    echo "        <div class=\"title\">Password Reset</div>\n";
    echo "        <div class=\"content\" id=\"password_reset_form\">\n";
    if (!empty($sPasswordResetFailed)) {
      echo "        <p>" . $sPasswordResetFailed . "</p>\n";
    } else if ($bPasswordResetSuccess) {
      echo "        <p>You account has been activated and you may now log in.</p>\n";
    } else {
      echo "        <p>Enter the verification id given to you in your email.</p>\n";
    }
    echo "          <label for=\"id\" title=\"Enter the verification id given to you in your email.\">Verification Id:</label>\n";
    echo "          <input id=\"id\" name=\"id\" type=\"text\" tabindex=\"1\" autofocus=\"autofocus\" required=\"required\" title=\"Enter the verification id given to you in your email.\" value=\"" . htmlspecialchars($sId, ENT_XML1, "UTF-8") . "\" />\n";
    echo "          <input type=\"submit\" tabindex=\"2\" title=\"Click to validate your account.\" value=\"Validate\" />\n";
    echo "        </div>\n";
    echo "      </form>\n";
  