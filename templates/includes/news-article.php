<?php
  $sPageTitle = 'News';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/news_item.css', true);
  
  $aOpenGraphItems["og:type"]                 = "article";
  $aOpenGraphItems["og:title"]                = ContentFilter::fFilterHTML($aArticle['title']);
  $aOpenGraphItems["og:description"]          = ContentFilter::fFilterNewLines(ContentFilter::fFilterHTML(ContentFilter::fTrimArticleContent($aArticle['content'], 2)), true, " ");
  $aOpenGraphItems["og:image"]                = BNETDocs::fGetCurrentFullURL('/news_category_' . urlencode($aArticle['category_id']) . '.png', true);
  $aOpenGraphItems["article:published_time"]  = date("c", strtotime($aArticle['post_date']));
  if (!is_null($aArticle['edit_date']))
    $aOpenGraphItems["article:modified_time"] = date("c", strtotime($aArticle['edit_date']));
  $aOpenGraphItems["article:section"]         = urlencode($aArticle['category_name']);
  
  include('./includes/header.php');
  include('./includes/news-article-ajax.php');
  include('./includes/footer.php');
  