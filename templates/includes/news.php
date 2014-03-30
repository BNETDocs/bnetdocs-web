<?php
  $sPageTitle = 'News';
  $sPageAdditionalStyle = BnetDocs::fGetCurrentFullURL('/news_item.css', true);
  include('./includes/header.php');
  foreach ($aNews as $aNewsItem) {
    echo "      <div class=\"news_item\">\n";
    echo "        <a class=\"title\" href=\"" . BnetDocs::fGetCurrentFullURL('/news/' . urlencode($aNewsItem['id']), true) . "\">"
                  . ContentFilter::fFilterHTML($aNewsItem['title'])
                  . "</a>\n";
    echo "        <div class=\"content\">" . ContentFilter::fFilterNewLines(ContentFilter::fFilterHTML($aNewsItem['content'], true)) . "</div>\n";
    echo "        <div class=\"footer\">\n";
    echo "          <span class=\"left\">" . ContentFilter::fFilterHTML($aNewsItem['creator']) . "</span>\n";
    echo "          <span class=\"right\">" . ContentFilter::fFilterHTML(date('D, M jS, Y g:i:s A T', strtotime($aNewsItem['pub_date']))) . "</span>\n";
    echo "        </div>\n";
    echo "      </div>\n";
  }
  include('./includes/footer.php'); ?>