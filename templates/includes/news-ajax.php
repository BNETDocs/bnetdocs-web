<?php
  $sPageTitle = 'News - BNETDocs';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/news_item.css', true);
  $oContext->fSetResponseHeader('X-Page-Title', $sPageTitle);
  $oContext->fSetResponseHeader('X-Page-Extra-Style', $sPageAdditionalStyle);
  foreach ($aNews as $aNewsItem) {
    echo "      <article" . ($aNewsItem['options'] & 0x02 ? " class=\"red\"" : "") . " id=\"n" . urlencode($aNewsItem['id']) . "\">\n";
    if (!($aNewsItem['options'] & 0x01)) {
      echo "        <a href=\"https://twitter.com/share?text=" . urlencode($aNewsItem['title']) . "&amp;url=" . BNETDocs::fGetCurrentFullURL('/news/' . urlencode($aNewsItem['id']), true) . "\" rel=\"external\"><img class=\"social-button\" title=\"Share on Twitter\" alt=\"Share on Twitter\" src=\"/Social-Twitter-24x24.png\" /></a>\n";
      echo "        <a href=\"https://facebook.com/sharer/sharer.php?u=" . BNETDocs::fGetCurrentFullURL('/news/' . urlencode($aNewsItem['id']), true) . "\" rel=\"external\"><img class=\"social-button\" title=\"Share on Facebook\" alt=\"Share on Facebook\" src=\"/Social-Facebook-24x24.png\" /></a>\n";
    }
    echo "        <a class=\"title\" href=\"" . BNETDocs::fGetCurrentFullURL('/news/' . urlencode($aNewsItem['id']), true) . "\">"
                  . ContentFilter::fFilterHTML($aNewsItem['title'])
                  . "</a>\n";
    echo "        <section>"
                  . "<img title=\"" . ContentFilter::fFilterHTML($aNewsItem['category_name']) . "\" alt=\"" . ContentFilter::fFilterHTML($aNewsItem['category_name']) . "\" src=\"/news_category_" . urlencode($aNewsItem['category_id']) . ".png\" />"
                  . ContentFilter::fFilterNewLines(ContentFilter::fFilterHTML($aNewsItem['content'], true))
                  . "</section>\n";
    echo "        <footer>\n";
    echo "          <div class=\"left\">" . ContentFilter::fFilterHTML($aNewsItem['creator']) . "</div>\n";
    echo "          <div class=\"right\">" . ContentFilter::fFilterHTML(date('D, M jS, Y g:i:s A T', strtotime($aNewsItem['pub_date']))) . "</div>\n";
    echo "        </footer>\n";
    echo "      </article>\n";
  }
  