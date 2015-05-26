<?php

namespace BNETDocs\Models;

use BNETDocs\Libraries\Common;
use BNETDocs\Libraries\Database;
use BNETDocs\Libraries\Model;

class News extends Model {

  public $news_posts;

  public function __construct() {
    $this->news_posts = [
      0 => [
        "id" => 0,
        "author" => "Carl Bennett",
        "timestamp_published" => new \DateTime("2015-05-25 21:53:00 CDT"),
        "title" => "Work in progress",
        "content" => "I've been giving life back into BNETDocs: Phoenix recently. There's been lots of changes to the code repository and restructuring it. There's been lots of new designs and paradigms put in place that are better than the previous Phoenix from last year. More news coming soon."
      ]
    ];
  }

}
