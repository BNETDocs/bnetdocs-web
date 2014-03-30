<?php
  $sPageTitle = 'News';
  $sPageAdditionalStyle = BnetDocs::fGetCurrentFullURL('/news_full.css');
  include('./includes/header.php');
?>    <div style="margin-top:20px;margin-bottom:20px;background:#f0f0f0;border-radius:8px;padding:8px;">
<?php
      foreach ($aNews as $aNewsItem) {
        echo "      <div class=\"newsitem\">\n";
        echo "        <div class=\"title\">"
                   .   "<a href=\"" . BnetDocs::fGetCurrentFullURL('/news/' . urlencode($aNewsItem['id'])) . "\">"
                   .   ContentFilter::fFilterHTML($aNewsItem['title'])
                   .   "</a>"
                   . "</div>\n";
        echo "        <div class=\"content\">" . ContentFilter::fFilterBBCode(ContentFilter::fFilterHTML($aNewsItem['content'])) . "</div>\n";
        echo "        <div class=\"creator\">" . ContentFilter::fFilterHTML($aNewsItem['creator']) . "</div>\n";
        echo "        <div class=\"post_date\">" . ContentFilter::fFilterHTML($aNewsItem['post_date']) . "</div>\n";
        echo "      </div>\n";
      }
?>    </div>
<?php include('./includes/footer.php'); ?>