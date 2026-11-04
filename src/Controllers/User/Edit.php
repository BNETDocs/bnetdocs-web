<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Exceptions\InvalidModelException;
use \BNETDocs\Libraries\Core\Config;
use \BNETDocs\Libraries\Core\HttpCode;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\EventLog\EventTypes;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Libraries\User\Profile;
use \BNETDocs\Libraries\User\User;
use \BNETDocs\Libraries\User\VerificationEmail;
use \BNETDocs\Models\User\Edit as EditModel;

class Edit extends \BNETDocs\Controllers\Base
{
    private ?Profile $profile = null;

    public function __construct()
    {
        $this->model = new EditModel();
    }

    public function invoke(?array $args): bool
    {
        $model = $this->model();

        if (!$model->active_user)
        {
            $model->_responseCode = HttpCode::HTTP_FORBIDDEN;
            $model->error = EditModel::ERROR_NOT_LOGGED_IN;
            return true;
        }

        $model->acl_allowed = $model->active_user->getOption(User::OPTION_ACL_USER_MODIFY);
        if (!$model->acl_allowed)
        {
            $model->_responseCode = HttpCode::HTTP_FORBIDDEN;
            $model->error = EditModel::ERROR_ACL_NOT_SET;
            return true;
        }

        $model->form_fields = Router::query();
        $id = $model->form_fields['id'] ?? null;
        $model->target_id = \is_numeric($id) ? (int) $id : null;

        try
        {
            if (!\is_null($model->target_id))
            {
                $model->target_user = new User($model->target_id);
            }
        }
        catch (\BNETDocs\Exceptions\UserNotFoundException | \OutOfBoundsException)
        {
            $model->target_user = null;
        }

        if (!$model->target_user)
        {
            $model->_responseCode = HttpCode::HTTP_NOT_FOUND;
            $model->error = EditModel::ERROR_NOT_FOUND;
            return true;
        }

        $editor = $model->active_user;
        $target = $model->target_user;
        $model->is_self = ($target->getId() === $editor->getId());

        // Editing an account that holds permissions the editor lacks would allow taking it over
        // (e.g. by changing its email and then resetting its password), so refuse it entirely.
        $outranking_bits = $target->getOptions() & ~$editor->getOptions() & EditModel::ACL_MASK;
        if (!$model->is_self && $outranking_bits !== 0)
        {
            $model->_responseCode = HttpCode::HTTP_FORBIDDEN;
            $model->error = EditModel::ERROR_OUTRANKED;
            return true;
        }

        // Only permissions the editor holds can be granted or revoked; status flags are always editable.
        $editable = ($editor->getOptions() & EditModel::ACL_MASK) | User::OPTION_DISABLED | User::OPTION_VERIFIED;
        if ($model->is_self)
        {
            // prevent locking yourself out
            $editable &= ~(User::OPTION_ACL_USER_MODIFY | User::OPTION_DISABLED);
        }
        $model->editable_mask = $editable;

        $req = Config::get('bnetdocs.user_register_requirements') ?? [];
        $model->username_max_len = (int) ($req['username_length_max'] ?? User::MAX_USERNAME);
        $model->username_min_len = (int) ($req['username_length_min'] ?? 3);

        $model->display_name = $target->getDisplayName();
        $model->email = $target->getEmail();
        $model->options = $target->getOptions();
        $model->options_before = $target->getOptions();
        $model->username = $target->getUsername();

        $this->profile = $target->getUserProfile();
        if (!$this->profile)
        {
            $this->profile = new Profile(null);
            $this->profile->setUserId($target->getId());
        }
        foreach (\array_keys(Profile::FIELDS) as $field)
        {
            $model->profile[$field] = $this->profile->getField($field);
        }

        $model->error = null;
        $model->_responseCode = HttpCode::HTTP_OK;

        if (Router::requestMethod() == Router::METHOD_POST)
        {
            $this->handlePost();
        }

        return true;
    }

    private function handlePost(): void
    {
        $model = $this->model();
        $f = $model->form_fields;
        $target = $model->target_user;

        if (($f['action'] ?? null) === 'resend_verification')
        {
            $this->resendVerification();
            return;
        }

        $username = \trim(\is_string($f['username'] ?? null) ? $f['username'] : '');
        $display_name = \trim(\is_string($f['display_name'] ?? null) ? $f['display_name'] : '');
        $email = \trim(\is_string($f['email'] ?? null) ? $f['email'] : '');
        if ($display_name === '') $display_name = null;

        $submitted = 0;
        $submitted_options = $f['options'] ?? [];
        if (\is_array($submitted_options))
        {
            foreach ($submitted_options as $bit)
            {
                if (\is_string($bit) && \ctype_digit($bit)) $submitted |= (int) $bit;
            }
        }

        // keep every bit the editor may not change, take the rest from the form
        $editable = $model->editable_mask;
        $options = ($model->options_before & ~$editable) | ($submitted & $editable);

        $model->username = $username;
        $model->display_name = $display_name;
        $model->email = $email;
        $model->options = $options;

        $username_len = \strlen($username);
        if ($username_len === 0) $model->errors['username'] = 'EMPTY';
        else if ($username_len > $model->username_max_len) $model->errors['username'] = 'TOO_LONG';
        else if ($username_len < $model->username_min_len) $model->errors['username'] = 'TOO_SHORT';
        else if ($username !== $target->getUsername())
        {
            $existing = User::findIdByUsername($username);
            if (!\is_null($existing) && $existing !== $target->getId()) $model->errors['username'] = 'TAKEN';
        }

        if (!\is_null($display_name) && \strlen($display_name) > $model->display_name_max_len)
        {
            $model->errors['display_name'] = 'TOO_LONG';
        }

        if ($email === '') $model->errors['email'] = 'EMPTY';
        else if (\strlen($email) > $model->email_max_len) $model->errors['email'] = 'TOO_LONG';
        else if (!\filter_var($email, \FILTER_VALIDATE_EMAIL)) $model->errors['email'] = 'INVALID';
        else if (\strtolower($email) !== \strtolower($target->getEmail()))
        {
            $existing = User::findIdByEmail($email);
            if (!\is_null($existing) && $existing !== $target->getId()) $model->errors['email'] = 'TAKEN';
        }

        $submitted_profile = $f['profile'] ?? [];
        if (!\is_array($submitted_profile)) $submitted_profile = [];
        foreach (\array_keys(Profile::FIELDS) as $field)
        {
            $value = \trim(\is_string($submitted_profile[$field] ?? null) ? $submitted_profile[$field] : '');
            if ($value === '') $value = null;
            $model->profile[$field] = $value;

            if (\strlen($value ?? '') > $model->profile_max_len)
            {
                $model->errors['profile_' . $field] = 'TOO_LONG';
            }
        }

        if (!empty($model->errors))
        {
            $model->_responseCode = HttpCode::HTTP_BAD_REQUEST;
            return;
        }

        $changes = [];
        if ($username !== $target->getUsername()) $changes['username'] = [$target->getUsername(), $username];
        if ($display_name !== $target->getDisplayName()) $changes['display_name'] = [$target->getDisplayName(), $display_name];
        if ($email !== $target->getEmail()) $changes['email'] = [$target->getEmail(), $email];
        if ($options !== $target->getOptions()) $changes['options'] = [$target->getOptions(), $options];
        $user_changed = !empty($changes);

        foreach ($model->profile as $field => $value)
        {
            $old = $this->profile->getField($field);
            if ($value !== $old) $changes['profile'][$field] = [$old, $value];
        }

        if (empty($changes))
        {
            $model->saved = true;
            return;
        }

        if ($user_changed)
        {
            $target->setUsername($username);
            $target->setDisplayName($display_name);
            $target->setEmail($email);
            // toggle verified through setVerified() so its date and verifier token stay consistent
            $target->setOptions(($options & ~User::OPTION_VERIFIED) | ($target->getOptions() & User::OPTION_VERIFIED));
            $target->setVerified(($options & User::OPTION_VERIFIED) !== 0);

            if (!$target->commit())
            {
                $model->_responseCode = HttpCode::HTTP_INTERNAL_SERVER_ERROR;
                $model->error = EditModel::ERROR_INTERNAL;
                return;
            }
        }

        if (isset($changes['profile']))
        {
            foreach ($changes['profile'] as $field => [, $value])
            {
                $this->profile->setField($field, $value);
            }

            if (!$this->profile->commit())
            {
                $model->_responseCode = HttpCode::HTTP_INTERNAL_SERVER_ERROR;
                $model->error = EditModel::ERROR_INTERNAL;
                return;
            }
        }

        $model->saved = true;

        $before = $model->options_before;
        $granted = EditModel::optionNames($options & ~$before);
        $revoked = EditModel::optionNames($before & ~$options);

        $event = Logger::initEvent(
            EventTypes::USER_EDITED,
            $model->active_user,
            getenv('REMOTE_ADDR'),
            [
                'user_id' => $target->getId(),
                'changes' => $changes,
                'options_granted' => \array_values($granted),
                'options_revoked' => \array_values($revoked),
            ]
        );

        if ($event->commit())
        {
            $fields = [
                'Edited by' => $model->active_user->getAsMarkdown(),
                'Edited user' => $target->getAsMarkdown(),
            ];
            foreach (['username' => 'Username', 'display_name' => 'Display name'] as $key => $label)
            {
                if (!isset($changes[$key])) continue;
                $fields[$label] = \sprintf('%s → %s', $changes[$key][0] ?? '(none)', $changes[$key][1] ?? '(none)');
            }
            // the address itself stays in the event log, out of Discord
            if (isset($changes['email'])) $fields['Email'] = 'Changed';
            if (isset($changes['profile']))
            {
                $fields['Profile'] = \implode(', ', \array_map(
                    fn(string $field): string => EditModel::PROFILE_FIELDS[$field][0],
                    \array_keys($changes['profile'])
                ));
            }
            if (!empty($granted)) $fields['Granted'] = \implode(', ', $granted);
            if (!empty($revoked)) $fields['Revoked'] = \implode(', ', $revoked);

            $embed = Logger::initDiscordEmbed($event, $target->getURI(), $fields);
            Logger::logToDiscord($event, $embed);
        }

        $model->options_before = $options;
    }

    private function resendVerification(): void
    {
        $model = $this->model();
        $target = $model->target_user;

        if ($target->isVerified())
        {
            $model->_responseCode = HttpCode::HTTP_CONFLICT;
            $model->verification_email = EditModel::VERIFICATION_ALREADY_VERIFIED;
            return;
        }

        try
        {
            // an unverified user can lack a token, e.g. one unverified before tokens were kept in sync
            if (empty($target->getVerifierToken()))
            {
                $target->setVerified(false, true);
                if (!$target->commit()) throw new \RuntimeException('failed to save a new verifier token');
            }

            VerificationEmail::send($target, $model->active_user, true);
            $model->verification_email = EditModel::VERIFICATION_SENT;
        }
        catch (\Throwable $e)
        {
            \error_log(\sprintf('Failed to resend verification email to user #%d: %s', $target->getId(), $e->getMessage()));
            $model->_responseCode = HttpCode::HTTP_INTERNAL_SERVER_ERROR;
            $model->verification_email = EditModel::VERIFICATION_FAILED;
        }
    }

    /**
     * Retrieves this controller's model as its concrete type.
     *
     * @return EditModel The model.
     * @throws InvalidModelException if the model is not an EditModel.
     */
    private function model(): EditModel
    {
        if (!$this->model instanceof EditModel)
        {
            throw new InvalidModelException($this->model ?? EditModel::class);
        }
        return $this->model;
    }
}
