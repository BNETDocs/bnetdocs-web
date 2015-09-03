<?php

namespace BNETDocs\Models;

use \BNETDocs\Libraries\Model;

class News extends Model {

  public $news_posts;

  public function __construct() {
    parent::__construct();
    $this->news_posts = [];
  }

}
