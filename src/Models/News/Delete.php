<?php

namespace BNETDocs\Models\News;

class Delete extends \BNETDocs\Models\ActiveUser
{
  public bool $acl_allowed;
  public ?int $id;
  public ?\BNETDocs\Libraries\NewsPost $news_post;
  public string $title;
  public ?\BNETDocs\Libraries\User $user;
}
