<?php

namespace BNETDocs\Models\Comment;

class Edit extends \BNETDocs\Models\ActiveUser
{
  public $acl_allowed;
  public $comment;
  public $content;
  public $id;
  public $parent_id;
  public $parent_type;
  public $return_url;
  public $user;
}
