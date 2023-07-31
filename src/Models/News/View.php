<?php

namespace BNETDocs\Models\News;

class View extends \BNETDocs\Models\ActiveUser
{
  public bool $acl_allowed;
  public ?array $comments;
  public ?\BNETDocs\Libraries\NewsPost $news_post;
  public ?int $news_post_id;
}
