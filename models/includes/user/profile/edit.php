<?php
  $sPageTitle = 'Edit Profile';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/login_page.css', true);
  include('./includes/header.php');
  include('./includes/user/profile/edit-ajax.php');
  include('./includes/footer.php');
  