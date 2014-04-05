<?php
  $sPageTitle = 'Login';
  $sPageAdditionalStyle = BnetDocs::fGetCurrentFullURL('/login_page.css', true);
  include('./includes/header.php');
  include('./includes/user/login-ajax.php');
  include('./includes/footer.php');
  