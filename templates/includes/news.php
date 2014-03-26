<?php
  $sPageTitle = 'News';
  include('./includes/header.php');
?>    <div style="position:fixed;left:50%;width:400px;margin-left:-200px;margin-top:20px;margin-bottom:20px;background:#f0f0f0;border-radius:8px;padding:8px;">
      <?php echo str_replace("\n", "<br/>\n      ", json_encode($aNews, JSON_PRETTY_PRINT)); ?>
    </div>
<?php include('./includes/footer.php'); ?>