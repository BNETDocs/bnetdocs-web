<?php

namespace BNETDocs\Models\News;

use \CarlBennett\MVC\Libraries\Model;

class Delete extends Model {

  public $acl_allowed;
  public $csrf_id;
  public $csrf_token;
  public $error;
  public $id;
  public $news_post;
  public $title;
  public $user;

}
