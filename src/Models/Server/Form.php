<?php

namespace BNETDocs\Models\Server;

class Form extends \BNETDocs\Models\HttpForm implements \JsonSerializable
{
  public const ERROR_ACCESS_DENIED = 'ACCESS_DENIED';
  public const ERROR_INTERNAL = 'INTERNAL_ERROR';
  public const ERROR_INVALID_ADDRESS = 'INVALID_ADDRESS';
  public const ERROR_INVALID_ID = 'INVALID_ID';
  public const ERROR_INVALID_LABEL = 'INVALID_LABEL';
  public const ERROR_INVALID_PORT = 'INVALID_PORT';
  public const ERROR_INVALID_TYPE = 'INVALID_TYPE';
  public const ERROR_SUCCESS = 'SUCCESS';

  public ?\BNETDocs\Libraries\Server $server = null;
  public bool $server_edit = false;
  public ?array $server_types = null;

  public function jsonSerialize(): mixed
  {
    return \array_merge(parent::jsonSerialize(), [
      'server' => $this->server,
      'server_edit' => $this->server_edit,
      'server_types' => $this->server_types,
    ]);
  }
}
