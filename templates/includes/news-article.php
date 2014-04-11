<?php
  $sPageTitle = 'News';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/news_item.css', true);
  $aOpenGraphItems["og:type"]                = "article";
  $aOpenGraphItems["article:published_time"] = date("c", strtotime($aArticle['post_date']));
  $aOpenGraphItems["article:modified_time"]  = date("c", strtotime($aArticle['edit_date']));
  $aOpenGraphItems["article:section"]        = urlencode($aArticle['category_name']);
  include('./includes/header.php');
  include('./includes/news-article-ajax.php');
  include('./includes/footer.php');
  