<?php

namespace BNETDocs\Models\User;

class Verify extends \BNETDocs\Models\ActiveUser implements \JsonSerializable
{
  public mixed $error = 'INTERNAL_ERROR';
  public ?string $token = null;
  public ?\BNETDocs\Libraries\User $user = null;
  public int|string|null $user_id = null;

  public function jsonSerialize(): mixed
  {
    return \array_merge(parent::jsonSerialize(), [
      'token' => $this->token,
      'user' => $this->user,
      'user_id' => $this->user_id,
    ]);
  }
}
