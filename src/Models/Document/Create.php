<?php

namespace BNETDocs\Models\Document;

class Create extends \BNETDocs\Models\ActiveUser
{
  public bool $acl_allowed = false;
  public ?string $brief = null;
  public ?string $content = null;
  public bool $markdown = true;
  public ?string $title = null;
}
