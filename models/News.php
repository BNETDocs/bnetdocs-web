<?php

namespace BNETDocs\Models;

use BNETDocs\Libraries\Common;
use BNETDocs\Libraries\Database;
use BNETDocs\Libraries\Model;

class News extends Model {

  public $news_posts;

  public function __construct() {
    $this->news_posts = [];
  }

}
