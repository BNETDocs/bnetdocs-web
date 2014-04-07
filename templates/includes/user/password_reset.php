<?php
  $sPageTitle = 'Password Reset';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/login_page.css', true);
  include('./includes/header.php');
  include('./includes/user/password_reset-ajax.php');
  include('./includes/footer.php');
  