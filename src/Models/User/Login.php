<?php

namespace BNETDocs\Models\User;

class Login extends \BNETDocs\Models\ActiveUser implements \JsonSerializable
{
    public const ERROR_ALREADY_LOGGED_IN = 'ALREADY_LOGGED_IN';
    public const ERROR_EMPTY_EMAIL = 'EMPTY_EMAIL';
    public const ERROR_EMPTY_PASSWORD = 'EMPTY_PASSWORD';
    public const ERROR_INCORRECT_PASSWORD = 'INCORRECT_PASSWORD';
    public const ERROR_PASSWORD_RESET_REQUIRED = 'PASSWORD_RESET_REQUIRED';
    public const ERROR_SYSTEM_DISABLED = 'SYSTEM_DISABLED';
    public const ERROR_USER_DISABLED = 'USER_DISABLED';
    public const ERROR_USER_NOT_FOUND = 'USER_NOT_FOUND';
    public const ERROR_USER_NOT_VERIFIED = 'USER_NOT_VERIFIED';

    public ?string $email = null;
    public ?string $password = null;
    public ?\BNETDocs\Libraries\User\User $user = null;

    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'email' => $this->email,
            'password' => $this->password,
            'user' => $this->user,
        ]);
    }
}
