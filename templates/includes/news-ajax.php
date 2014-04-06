<?php
  $sPageTitle = 'News - BNETDocs';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/news_item.css', true);
  $oContext->fSetResponseHeader('X-Page-Title', $sPageTitle);
  $oContext->fSetResponseHeader('X-Page-Extra-Style', $sPageAdditionalStyle);
  foreach ($aNews as $aNewsItem) {
    echo "      <div class=\"news_item\" id=\"n" . urlencode($aNewsItem['id']) . "\">\n";
    echo "        <a href=\"javascript:alert('Not yet implemented.');\"><img class=\"social-button\" title=\"Share on Facebook\" alt=\"Share on Facebook\" src=\"/Social-Facebook-28x28.png\" /></a>\n";
    echo "        <a class=\"title\" href=\"" . BNETDocs::fGetCurrentFullURL('/news/' . urlencode($aNewsItem['id']), true) . "\">"
                  . ContentFilter::fFilterHTML($aNewsItem['title'])
                  . "</a>\n";
    echo "        <div class=\"content\">"
                  . "<img title=\"" . ContentFilter::fFilterHTML($aNewsItem['category_name']) . "\" alt=\"" . ContentFilter::fFilterHTML($aNewsItem['category_name']) . "\" src=\"/news_category_" . urlencode($aNewsItem['category_id']) . ".png\" />"
                  . ContentFilter::fFilterNewLines(ContentFilter::fFilterHTML($aNewsItem['content'], true))
                  . "</div>\n";
    echo "        <div class=\"footer\">\n";
    echo "          <span class=\"left\">" . ContentFilter::fFilterHTML($aNewsItem['creator']) . "</span>\n";
    echo "          <span class=\"right\">" . ContentFilter::fFilterHTML(date('D, M jS, Y g:i:s A T', strtotime($aNewsItem['pub_date']))) . "</span>\n";
    echo "        </div>\n";
    echo "      </div>\n";
  }
  