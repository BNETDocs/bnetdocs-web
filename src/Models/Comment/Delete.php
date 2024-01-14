<?php

namespace BNETDocs\Models\Comment;

class Delete extends \BNETDocs\Models\ActiveUser
{
  public bool $acl_allowed = false;
  public ?\BNETDocs\Libraries\Comment $comment = null;
  public ?string $content = null;
  public ?int $id = null;
  public ?int $parent_id = null;
  public ?int $parent_type = null;
}
