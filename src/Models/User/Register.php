<?php

namespace BNETDocs\Models\User;

class Register extends \BNETDocs\Models\ActiveUser implements \JsonSerializable
{
    public const ERROR_ALREADY_LOGGED_IN = 'ALREADY_LOGGED_IN';
    public const ERROR_COUNTRY_DENIED = 'COUNTRY_DENIED';
    public const ERROR_EMAIL_ALREADY_USED = 'EMAIL_ALREADY_USED';
    public const ERROR_EMAIL_FAILURE = 'EMAIL_FAILURE';
    public const ERROR_EMAIL_NOT_ALLOWED = 'EMAIL_NOT_ALLOWED';
    public const ERROR_INVALID_CAPTCHA = 'INVALID_CAPTCHA';
    public const ERROR_INVALID_EMAIL = 'INVALID_EMAIL';
    public const ERROR_NONMATCHING_PASSWORD = 'NONMATCHING_PASSWORD';
    public const ERROR_PASSWORD_CONTAINS_EMAIL = 'PASSWORD_CONTAINS_EMAIL';
    public const ERROR_PASSWORD_CONTAINS_USERNAME = 'PASSWORD_CONTAINS_USERNAME';
    public const ERROR_PASSWORD_DENYLIST = 'PASSWORD_DENYLIST';
    public const ERROR_PASSWORD_TOO_LONG = 'PASSWORD_TOO_LONG';
    public const ERROR_PASSWORD_TOO_SHORT = 'PASSWORD_TOO_SHORT';
    public const ERROR_REGISTER_DISABLED = 'REGISTER_DISABLED';
    public const ERROR_USERNAME_TAKEN = 'USERNAME_TAKEN';
    public const ERROR_USERNAME_TOO_LONG = 'USERNAME_TOO_LONG';
    public const ERROR_USERNAME_TOO_SHORT = 'USERNAME_TOO_SHORT';

    public ?string $denylist_reason = null;
    public ?string $email = null;
    public ?string $honeypot = null;
    public ?\BNETDocs\Libraries\Core\Recaptcha $recaptcha = null;
    public ?string $username = null;
    public int $username_max_len = 0;

    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'denylist_reason' => $this->denylist_reason,
            'email' => $this->email,
            'honeypot' => $this->honeypot,
            'recaptcha' => $this->recaptcha,
            'username' => $this->username,
            'username_max_len' => $this->username_max_len,
        ]);
    }
}
