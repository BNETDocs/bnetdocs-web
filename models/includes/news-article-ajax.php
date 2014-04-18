<?php
  $sPageTitle = 'News - BNETDocs';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/news_item.css', true);
  $oContext->fSetResponseHeader('X-Page-Title', $sPageTitle);
  $oContext->fSetResponseHeader('X-Page-Extra-Style', $sPageAdditionalStyle);
  echo "      <article class=\"go_back\">\n";
  echo "        <a class=\"title\" href=\"" . BNETDocs::fGetCurrentFullURL('/news#n' . urlencode($aArticle['id']), true) . "\">&lt; Back to news articles</a>\n";
  echo "      </article>\n";
  
  if (!$aArticle) {
    $oContext->fSetResponseCode(404);
    echo "      <article>\n";
    echo "        <h1>No Article</h1>\n";
    echo "        <section>The article you tried accessing could not be found in our database.</section>\n";
    echo "      </article>\n";
  } else {
    echo "      <article id=\"n" . urlencode($aArticle['id']) . "\">\n";
    echo "        <a href=\"https://twitter.com/share?text=" . urlencode($aArticle['title']) . "&amp;url=" . urlencode(BNETDocs::fGetCurrentFullURL('', true)) . "\" rel=\"external\"><img class=\"social-button\" title=\"Share on Twitter\" alt=\"Share on Twitter\" src=\"/Social-Twitter-24x24.png\" /></a>\n";
    echo "        <a href=\"https://facebook.com/sharer/sharer.php?u=" . urlencode(BNETDocs::fGetCurrentFullURL('', true)) . "\" rel=\"external\"><img class=\"social-button\" title=\"Share on Facebook\" alt=\"Share on Facebook\" src=\"/Social-Facebook-24x24.png\" /></a>\n";
    echo "        <a class=\"title\" href=\"" . BNETDocs::fGetCurrentFullURL('/news/' . urlencode($aArticle['id']), true) . "\">"
                  . ContentFilter::fFilterHTML($aArticle['title'])
                  . "</a>\n";
    echo "        <section>"
                  . "<img title=\"" . ContentFilter::fFilterHTML($aArticle['category_name']) . "\" alt=\"" . ContentFilter::fFilterHTML($aArticle['category_name']) . "\" src=\"/news_category_" . urlencode($aArticle['category_id']) . ".png\" />"
                  . ContentFilter::fFilterNewLines(ContentFilter::fFilterHTML($aArticle['content'], true))
                  . "</section>\n";
    echo "        <footer>\n";
    echo "          <div class=\"left\">" . ContentFilter::fFilterHTML($aArticle['creator']) . "</div>\n";
    echo "          <div class=\"right\">" . ContentFilter::fFilterHTML(date('D, M jS, Y g:i:s A T', strtotime($aArticle['pub_date']))) . "</div>\n";
    echo "        </footer>\n";
    echo "      </article>\n";
  }
      
  if (!$aComments) {
    echo "      <article class=\"comment\">\n";
    echo "        <h1>No Comments</h1>\n";
    echo "        <section>There are no comments for this news article.</section>\n";
    echo "      </article>\n";
  } else {
    foreach ($aComments as $aComment) {
      echo "      <article class=\"comment\" id=\"c" . urlencode($aComment['id']) . "\">\n";
      echo "        <h1>\n";
      echo "          <div class=\"left\">" . ContentFilter::fFilterHTML($aComment['creator']) . "</div>\n";
      echo "          <div class=\"right\">" . ContentFilter::fFilterHTML(date('D, M jS, Y g:i:s A T', strtotime($aComment['pub_date']))) . "</div>\n";
      echo "        </h1>\n";
      echo "        <section>"
                    . ContentFilter::fFilterNewLines(ContentFilter::fFilterHTML($aComment['content'], true))
                    . "</section>\n";;
      echo "      </article>\n";
    }
  }
  