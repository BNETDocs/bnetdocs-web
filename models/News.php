<?php

namespace BNETDocs\Models;

use \BNETDocs\Libraries\Model;

class News extends Model {

  public $cache_date;
  public $news_posts;

  public function __construct() {
    parent::__construct();
    $this->cache_date = null;
    $this->news_posts = [];
  }

}
