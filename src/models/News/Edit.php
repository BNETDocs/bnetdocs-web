<?php

namespace BNETDocs\Models\News;

use \CarlBennett\MVC\Libraries\Model;

class Edit extends Model {

  public $acl_allowed;
  public $category;
  public $content;
  public $csrf_id;
  public $csrf_token;
  public $error;
  public $markdown;
  public $news_categories;
  public $news_post;
  public $news_post_id;
  public $published;
  public $title;
  public $user;
  public $user_session;

}
