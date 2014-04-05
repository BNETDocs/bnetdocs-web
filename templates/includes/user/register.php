<?php
  $sPageTitle = 'Register';
  $sPageAdditionalStyle = BnetDocs::fGetCurrentFullURL('/login_page.css', true);
  include('./includes/header.php');
  include('./includes/user/register-ajax.php');
  include('./includes/footer.php');
  