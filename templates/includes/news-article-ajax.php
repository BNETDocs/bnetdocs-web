<?php
  $sPageTitle = 'News - BNETDocs';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/news_item.css', true);
  $oContext->fSetResponseHeader('X-Page-Title', $sPageTitle);
  $oContext->fSetResponseHeader('X-Page-Extra-Style', $sPageAdditionalStyle);
  echo "      <div class=\"news_back\">\n";
  echo "        <a class=\"title\" href=\"" . BNETDocs::fGetCurrentFullURL('/news#n' . urlencode($aArticle['id']), true) . "\">&lt; Back to news articles</a>\n";
  echo "      </div>\n";
  
  if (!$aArticle) {
    $oContext->fSetResponseCode(404);
    echo "      <div class=\"news_item\">\n";
    echo "        <div class=\"title\">No Article</div>\n";
    echo "        <div class=\"content\">The article you tried accessing could not be found in our database.</div>\n";
    echo "      </div>\n";
  } else {
    echo "      <div class=\"news_item\" id=\"n" . urlencode($aArticle['id']) . "\">\n";
    echo "        <a href=\"https://twitter.com/share?text=" . urlencode($aArticle['title']) . "&amp;url=" . urlencode(BNETDocs::fGetCurrentFullURL('', true)) . "\" rel=\"external\"><img class=\"social-button\" title=\"Share on Twitter\" alt=\"Share on Twitter\" src=\"/Social-Twitter-24x24.png\" /></a>\n";
    echo "        <a href=\"https://facebook.com/sharer/sharer.php?u=" . urlencode(BNETDocs::fGetCurrentFullURL('', true)) . "\" rel=\"external\"><img class=\"social-button\" title=\"Share on Facebook\" alt=\"Share on Facebook\" src=\"/Social-Facebook-24x24.png\" /></a>\n";
    echo "        <a class=\"title\" href=\"" . BNETDocs::fGetCurrentFullURL('/news/' . urlencode($aArticle['id']), true) . "\">"
                  . ContentFilter::fFilterHTML($aArticle['title'])
                  . "</a>\n";
    echo "        <div class=\"content\">"
                  . "<img title=\"" . ContentFilter::fFilterHTML($aArticle['category_name']) . "\" alt=\"" . ContentFilter::fFilterHTML($aArticle['category_name']) . "\" src=\"/news_category_" . urlencode($aArticle['category_id']) . ".png\" />"
                  . ContentFilter::fFilterNewLines(ContentFilter::fFilterHTML($aArticle['content'], true))
                  . "</div>\n";
    echo "        <div class=\"footer\">\n";
    echo "          <span class=\"left\">" . ContentFilter::fFilterHTML($aArticle['creator']) . "</span>\n";
    echo "          <span class=\"right\">" . ContentFilter::fFilterHTML(date('D, M jS, Y g:i:s A T', strtotime($aArticle['pub_date']))) . "</span>\n";
    echo "        </div>\n";
    echo "      </div>\n";
  }
      
  if (!$aComments) {
    echo "      <div class=\"news_comment\">\n";
    echo "        <div class=\"title\">No Comments</div>\n";
    echo "        <div class=\"content\">There are no comments for this news article.</div>\n";
    echo "      </div>\n";
  } else {
    foreach ($aComments as $aComment) {
      echo "      <div class=\"news_comment\" id=\"c" . urlencode($aComment['id']) . "\">\n";
      echo "        <div class=\"title\">\n";
      echo "          <span class=\"left\">" . ContentFilter::fFilterHTML($aComment['creator']) . "</span>\n";
      echo "          <span class=\"right\">" . ContentFilter::fFilterHTML(date('D, M jS, Y g:i:s A T', strtotime($aComment['pub_date']))) . "</span>\n";
      echo "        </div>\n";
      echo "        <div class=\"content\">"
                    . ContentFilter::fFilterNewLines(ContentFilter::fFilterHTML($aComment['content'], true))
                    . "</div>\n";;
      echo "      </div>\n";
    }
  }
  