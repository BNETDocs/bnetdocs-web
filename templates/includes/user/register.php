<?php
  $sPageTitle = 'Register';
  $sPageAdditionalStyle = '';
  include('./includes/header.php');
?>    <div style="position:fixed;left:50%;width:400px;margin-left:-200px;margin-top:20px;margin-bottom:20px;background:#f0f0f0;border-radius:8px;padding:8px;">
      <div><?php if ($stat) { ?>Registration email sent<?php } else { ?>Registration email did not send<?php } ?></div>
    </div>
<?php include('./includes/footer.php'); ?>