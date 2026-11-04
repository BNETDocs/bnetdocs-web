<?php

namespace BNETDocs\Models\User;

class Verify extends \BNETDocs\Models\ActiveUser implements \JsonSerializable
{
    public const ERROR_ALREADY_VERIFIED = 'ALREADY_VERIFIED';
    public const ERROR_INVALID_TOKEN = 'INVALID_TOKEN';

    public ?string $token = null;
    public ?\BNETDocs\Libraries\User\User $user = null;
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
