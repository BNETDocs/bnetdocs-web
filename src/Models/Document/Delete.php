<?php

namespace BNETDocs\Models\Document;

class Delete extends \BNETDocs\Models\ActiveUser
{
  public const ERROR_ACCESS_DENIED = 'ACCESS_DENIED';
  public const ERROR_INTERNAL = 'INTERNAL';
  public const ERROR_NONE = 'NONE';
  public const ERROR_NOT_FOUND = 'NOT_FOUND';
  public const ERROR_SUCCESS = 'SUCCESS';

  public bool $acl_allowed = false;
  public ?\BNETDocs\Libraries\Document $document = null;
  public mixed $error = self::ERROR_NONE;
  public ?int $id = null;
  public ?string $title = null;
}
