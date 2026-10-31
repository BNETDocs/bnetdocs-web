<?php

namespace BNETDocs\Models\User;

class ChangePassword extends \BNETDocs\Models\Core\AccessControl implements \JsonSerializable
{
    public const ERROR_NONMATCHING_PASSWORD = 'NONMATCHING_PASSWORD';
    public const ERROR_PASSWORD_CONTAINS_EMAIL = 'PASSWORD_CONTAINS_EMAIL';
    public const ERROR_PASSWORD_CONTAINS_USERNAME = 'PASSWORD_CONTAINS_USERNAME';
    public const ERROR_PASSWORD_DENYLIST = 'PASSWORD_DENYLIST';
    public const ERROR_PASSWORD_INCORRECT = 'PASSWORD_INCORRECT';
    public const ERROR_PASSWORD_TOO_LONG = 'PASSWORD_TOO_LONG';
    public const ERROR_PASSWORD_TOO_SHORT = 'PASSWORD_TOO_SHORT';

    public ?string $denylist_reason = null;

    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), ['denylist_reason' => $this->denylist_reason]);
    }
}
