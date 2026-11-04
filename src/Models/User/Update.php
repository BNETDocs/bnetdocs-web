<?php

namespace BNETDocs\Models\User;

use \BNETDocs\Libraries\User\Profile;
use \BNETDocs\Libraries\User\User;

class Update extends \BNETDocs\Models\ActiveUser implements \JsonSerializable
{
    public const VERIFICATION_ALREADY_VERIFIED = 'ALREADY_VERIFIED';
    public const VERIFICATION_COOLDOWN = 'COOLDOWN';
    public const VERIFICATION_FAILED = 'FAILED';
    public const VERIFICATION_SENT = 'SENT';

    /**
     * The user being updated, which is always the active user.
     */
    public ?User $user = null;

    /**
     * Field name => error code, for fields that failed validation. Profile fields are named 'profile_' . field.
     */
    public array $errors = [];

    public bool $saved = false;

    /**
     * The outcome of sending a verification email, one of VERIFICATION_*, or null if none was sent.
     */
    public ?string $verification_email = null;

    public ?string $display_name = null;
    public int $display_name_max_len = User::MAX_DISPLAY_NAME;
    public ?string $email = null;
    public ?string $email_confirm = null;
    public int $email_max_len = User::MAX_EMAIL;
    public ?string $username = null;
    public int $username_max_len = User::MAX_USERNAME;
    public int $username_min_len = 3;

    /**
     * Public profile field => value, keyed by Profile::FIELDS.
     */
    public array $profile = [];
    public int $profile_max_len = Profile::MAX_LEN;

    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'display_name' => $this->display_name,
            'email' => $this->email,
            'errors' => $this->errors,
            'profile' => $this->profile,
            'saved' => $this->saved,
            'user' => $this->user,
            'username' => $this->username,
            'verification_email' => $this->verification_email,
        ]);
    }
}
