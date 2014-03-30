<?php
  
  $oResult = false;
  $aNews   = array();
  
  $oResult = BnetDocs::$oDB->fQuery('SELECT '
    . 'n.`id` AS `id`,'
    . 'IFNULL(u.`display_name`, u.`username`) AS `creator`,'
    . 'IFNULL(n.`edit_date`, n.`post_date`) AS `pub_date`,'
    . 'n.`edit_count` AS `edit_count`,'
    . 'c.`id` AS `category_id`,'
    . 'c.`display_name` AS `category_name`,'
    . 'n.`title` AS `title`,'
    . 'n.`content` AS `content` '
    . 'FROM `news_posts` n '
    . 'LEFT JOIN `users` u '
    . 'ON n.`creator_uid` = u.`uid` '
    . 'LEFT JOIN `news_categories` c '
    . 'ON n.`category` = c.`id` '
    . 'ORDER BY `pub_date` DESC, n.`id` DESC '
    . 'LIMIT 5;');
  
  if ($oResult && $oResult instanceof MySQLResult) {
    $aNews[] = array(
      'id'            => 0,
      'creator'       => 'Carl Bennett',
      'pub_date'      => date('Y-m-d H:i:s T'),
      'edit_count'    => 0,
      'category_id'   => 6,
      'category_name' => 'BNETDocs',
      'title'         => 'New BNETDocs Site!',
      'content'       => "Hi there, BNETDocs user!\r\n\r\n"
        . "If you are a commoner, you should have noticed that there has been some changes. "
        . "The old BNETDocs has been scrapped in favor of this new one. The reasons for this "
        . "were because of many security holes in the older core, so it was deemed better to "
        . "just start over fresh and convert the data over.\r\n\r\n"
        . "As with any conversion of data, there may be a few hiccups along the way. The "
        . "biggest hiccup I noticed was that the new design, while being more sturdy and "
        . "having room for more data analytics, doesn't have the analytics for the data "
        . "that was converted; an example of this is news posts that have been edited—"
        . "there is no original publication date for these posts. Please take note of this "
        . "as you explore the new site.\r\n\r\n"
        . "As part of the conversion process, I have reset the passwords for everyone. The "
        . "new site uses a different hashing algorithm than the old one, so this was a "
        . "necessary change. Follow the instructions at the login page to get your account "
        . "back.\r\n\r\n"
        . "Thanks!",
    );
    while ($aRow = $oResult->fFetchAssoc()) {
      $aNews[] = $aRow;
    }
  } else {
    $aNews[] = array(
      'id'            => 0,
      'creator'       => 'n/a',
      'pub_date'      => date('Y-m-d H:i:s T'),
      'edit_count'    => 0,
      'category_id'   => 6,
      'category_name' => 'BNETDocs',
      'title'         => 'ERROR RETRIEVING NEWS',
      'content'       => 'An error has occurred while retrieving the news.',
    );
  }
  
  ob_start('ob_gzhandler');
  include('./includes/news.php');
  $sPage = ob_get_clean();
  
  $oContext->fSetResponseCode(200);
  //$oContext->fSetResponseHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
  $oContext->fSetResponseHeader('Content-Type', 'application/xml;charset=utf-8');
  $oContext->fSetResponseContent($sPage);
  