<?php

namespace BNETDocs\Models\News;

class Edit extends \BNETDocs\Models\ActiveUser
{
  public bool $acl_allowed = false;
  public ?int $category = null;
  public ?array $comments = null;
  public ?string $content = null;
  public bool $markdown = false;
  public ?array $news_categories = null;
  public ?\BNETDocs\Libraries\NewsPost $news_post;
  public ?int $news_post_id = null;
  public bool $published = false;
  public bool $rss_exempt = true;
  public ?string $title = null;
}
