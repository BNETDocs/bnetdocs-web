<?php
  $sPageTitle = 'Register';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/login_page.css', true);
  include('./includes/header.php');
  include('./includes/user/register-ajax.php');
  include('./includes/footer.php');
  