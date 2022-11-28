<?php

namespace BNETDocs\Models\Server;

class Delete extends \BNETDocs\Models\ActiveUser implements \JsonSerializable
{
  public const ERROR_ACCESS_DENIED = 'ACCESS_DENIED';
  public const ERROR_INTERNAL = 'INTERNAL_ERROR';
  public const ERROR_INVALID_ID = 'INVALID_ID';
  public const ERROR_SUCCESS = 'SUCCESS';

  public ?\BNETDocs\Libraries\Server $server = null;

  public function jsonSerialize(): mixed
  {
    return \array_merge(parent::jsonSerialize(), ['server' => $this->server]);
  }
}
