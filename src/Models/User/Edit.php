<?php

namespace BNETDocs\Models\User;

use \BNETDocs\Libraries\User\User;

class Edit extends \BNETDocs\Models\Core\HttpForm implements \JsonSerializable
{
    public const ERROR_NOT_FOUND = 'NOT_FOUND';
    public const ERROR_OUTRANKED = 'OUTRANKED';

    /**
     * Account status flags, shown separately from the permission grid.
     */
    public const STATUS_OPTIONS = [
        User::OPTION_DISABLED => ['Disabled', 'Login is denied and any active sessions are expired.'],
        User::OPTION_VERIFIED => ['Verified', 'The email address was confirmed by a token sent to it.'],
    ];

    /**
     * Columns of the permission grid.
     */
    public const ACL_COLUMNS = ['Create', 'View', 'Modify', 'Delete'];

    /**
     * Rows of the permission grid, one option per column, null where it does not apply.
     */
    public const ACL_ROWS = [
        'Comments'   => [User::OPTION_ACL_COMMENT_CREATE, null, User::OPTION_ACL_COMMENT_MODIFY, User::OPTION_ACL_COMMENT_DELETE],
        'Documents'  => [User::OPTION_ACL_DOCUMENT_CREATE, null, User::OPTION_ACL_DOCUMENT_MODIFY, User::OPTION_ACL_DOCUMENT_DELETE],
        'Event logs' => [null, User::OPTION_ACL_EVENT_LOG_VIEW, User::OPTION_ACL_EVENT_LOG_MODIFY, User::OPTION_ACL_EVENT_LOG_DELETE],
        'News posts' => [User::OPTION_ACL_NEWS_CREATE, null, User::OPTION_ACL_NEWS_MODIFY, User::OPTION_ACL_NEWS_DELETE],
        'Packets'    => [User::OPTION_ACL_PACKET_CREATE, null, User::OPTION_ACL_PACKET_MODIFY, User::OPTION_ACL_PACKET_DELETE],
        'Servers'    => [User::OPTION_ACL_SERVER_CREATE, null, User::OPTION_ACL_SERVER_MODIFY, User::OPTION_ACL_SERVER_DELETE],
        'Users'      => [User::OPTION_ACL_USER_CREATE, null, User::OPTION_ACL_USER_MODIFY, User::OPTION_ACL_USER_DELETE],
    ];

    /**
     * Permissions that do not fit the grid.
     */
    public const ACL_OTHER = [
        User::OPTION_ACL_ANALYTICS_VIEW => ['View analytics', 'Access the analytics dashboard data.'],
        User::OPTION_ACL_PHPINFO        => ['View PHP info', 'Access the phpinfo() page, which exposes server configuration.'],
    ];

    /**
     * Every permission (non-status) bit shown on the page.
     */
    public const ACL_MASK = User::OPTION_STAFF | User::OPTION_ACL_ANALYTICS_VIEW;

    /**
     * Every bit shown on the page; bits outside of this are preserved as-is.
     */
    public const KNOWN_MASK = self::ACL_MASK | User::OPTION_DISABLED | User::OPTION_VERIFIED;

    /**
     * Bits the active user is permitted to change on the target user.
     */
    public int $editable_mask = 0;

    /**
     * Field name => error code, for fields that failed validation.
     */
    public array $errors = [];

    public bool $is_self = false;
    public bool $saved = false;

    public ?string $display_name = null;
    public int $display_name_max_len = User::MAX_DISPLAY_NAME;
    public ?string $email = null;
    public int $email_max_len = User::MAX_EMAIL;
    public int $options = 0;
    public int $options_before = 0;
    public ?string $username = null;
    public int $username_max_len = User::MAX_USERNAME;
    public int $username_min_len = 3;

    public ?int $target_id = null;
    public ?User $target_user = null;

    /**
     * Maps each set bit in a bitmask to its User::OPTION_* constant name.
     *
     * @param int $mask The bitmask.
     * @return array<int,string> Bit => name, with unrecognized bits named by their hex value.
     */
    public static function optionNames(int $mask): array
    {
        static $names = null;
        if (\is_null($names))
        {
            $names = [];
            foreach ((new \ReflectionClass(User::class))->getConstants() as $name => $value)
            {
                // single-bit OPTION_* constants only, which excludes presets like OPTION_STAFF
                if (\str_starts_with($name, 'OPTION_') && \is_int($value) && $value > 0 && ($value & ($value - 1)) === 0)
                {
                    $names[$value] = $name;
                }
            }
        }

        $r = [];
        for ($bit = 1; $bit > 0 && $bit <= $mask; $bit <<= 1)
        {
            if ($mask & $bit) $r[$bit] = $names[$bit] ?? \sprintf('0x%X', $bit);
        }
        return $r;
    }

    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'display_name' => $this->display_name,
            'editable_mask' => $this->editable_mask,
            'email' => $this->email,
            'errors' => $this->errors,
            'is_self' => $this->is_self,
            'options' => $this->options,
            'options_before' => $this->options_before,
            'saved' => $this->saved,
            'target_id' => $this->target_id,
            'target_user' => $this->target_user,
            'username' => $this->username,
        ]);
    }
}
