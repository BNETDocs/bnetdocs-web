<?php
  $sPageTitle = 'User Verify';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/login_page.css', true);
  include('./includes/header.php');
  include('./includes/user/verify-ajax.php');
  include('./includes/footer.php');
  