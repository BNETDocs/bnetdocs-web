<?php
  $sPageTitle = 'News';
  include('./includes/header.php');
?>    <div style="position:fixed;left:50%;width:400px;margin-left:-200px;margin-top:20px;margin-bottom:20px;background:#f0f0f0;border-radius:8px;padding:8px;">
<?php
      foreach ($aNews as $aNewsItem) {
        echo "      <strong>" . htmlspecialchars($aNewsItem['title'], ENT_QUOTES | ENT_DISALLOWED) . "</strong><br />\n";
        echo "      <code>" . htmlspecialchars($aNewsItem['content'], ENT_QUOTES | ENT_DISALLOWED) . "</code>\n";
      }
?>    </div>
<?php include('./includes/footer.php'); ?>