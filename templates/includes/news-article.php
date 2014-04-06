<?php
  $sPageTitle = 'News';
  $sPageAdditionalStyle = BNETDocs::fGetCurrentFullURL('/news_item.css', true);
  include('./includes/header.php');
  include('./includes/news-article-ajax.php');
  include('./includes/footer.php');
  