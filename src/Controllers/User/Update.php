<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */

namespace BNETDocs\Controllers\User;

use \BNETDocs\Exceptions\InvalidModelException;
use \BNETDocs\Libraries\Core\Config;
use \BNETDocs\Libraries\Core\HttpCode;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\Core\UrlFormatter;
use \BNETDocs\Libraries\EventLog\EventTypes;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Libraries\User\Profile;
use \BNETDocs\Libraries\User\User;
use \BNETDocs\Models\User\Update as UpdateModel;

class Update extends \BNETDocs\Controllers\Base
{
    private ?Profile $profile = null;

    public function __construct()
    {
        $this->model = new UpdateModel();
    }

    public function invoke(?array $args): bool
    {
        $model = $this->model();

        if (!$model->active_user)
        {
            $model->_responseCode = HttpCode::HTTP_FORBIDDEN;
            return true;
        }

        $model->_responseCode = HttpCode::HTTP_OK;
        $query = Router::query();

        // this page only edits the active user's own profile, staff edit other users at /user/edit
        $id = $query['id'] ?? null;
        if (\is_numeric($id) && (int) $id !== $model->active_user->getId()
            && $model->active_user->getOption(User::OPTION_ACL_USER_MODIFY))
        {
            $model->_responseCode = HttpCode::HTTP_FOUND;
            $model->_responseHeaders['Location'] = UrlFormatter::format('/user/edit?id=' . (int) $id);
            return true;
        }

        $user = $model->active_user;
        $model->user = $user;

        $req = Config::get('bnetdocs.user_register_requirements') ?? [];
        $model->username_max_len = (int) ($req['username_length_max'] ?? User::MAX_USERNAME);
        $model->username_min_len = (int) ($req['username_length_min'] ?? 3);

        $model->display_name = $user->getDisplayName();
        $model->email = $user->getEmail();
        $model->username = $user->getUsername();

        $this->profile = $user->getUserProfile();
        if (!$this->profile)
        {
            $this->profile = new Profile(null);
            $this->profile->setUserId($user->getId());
        }
        foreach (\array_keys(Profile::FIELDS) as $field)
        {
            $model->profile[$field] = $this->profile->getField($field);
        }

        if (Router::requestMethod() == Router::METHOD_POST)
        {
            $this->handlePost($query, $req);
        }

        return true;
    }

    private function handlePost(array $f, array $req): void
    {
        $model = $this->model();
        $user = $model->user;

        $string = fn(string $key): string => \trim(\is_string($f[$key] ?? null) ? $f[$key] : '');
        $username = $string('username');
        $display_name = $string('display_name');
        $email = $string('email');
        $email_confirm = $string('email_confirm');
        if ($display_name === '') $display_name = null;

        $model->username = $username;
        $model->display_name = $display_name;
        $model->email = $email;
        $model->email_confirm = $email_confirm;

        $username_len = \strlen($username);
        if ($username_len === 0) $model->errors['username'] = 'EMPTY';
        else if ($username_len > $model->username_max_len) $model->errors['username'] = 'TOO_LONG';
        else if ($username_len < $model->username_min_len) $model->errors['username'] = 'TOO_SHORT';
        else if ($username !== $user->getUsername())
        {
            $existing = User::findIdByUsername($username);
            if (!\is_null($existing) && $existing !== $user->getId()) $model->errors['username'] = 'TAKEN';
        }

        if (!\is_null($display_name) && \strlen($display_name) > $model->display_name_max_len)
        {
            $model->errors['display_name'] = 'TOO_LONG';
        }

        // a change of letter case alone is still the same mailbox
        $email_changed = \strtolower($email) !== \strtolower($user->getEmail());
        if ($email_changed)
        {
            if ($email === '') $model->errors['email'] = 'EMPTY';
            else if (\strlen($email) > $model->email_max_len) $model->errors['email'] = 'TOO_LONG';
            else if (!\filter_var($email, \FILTER_VALIDATE_EMAIL)) $model->errors['email'] = 'INVALID';
            else if (($req['email_enable_denylist'] ?? false) && $this->isEmailDenied($email)) $model->errors['email'] = 'NOT_ALLOWED';
            else
            {
                $existing = User::findIdByEmail($email);
                if (!\is_null($existing) && $existing !== $user->getId()) $model->errors['email'] = 'TAKEN';
            }

            if (!isset($model->errors['email']) && \strtolower($email_confirm) !== \strtolower($email))
            {
                $model->errors['email_confirm'] = 'MISMATCH';
            }
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
        if ($username !== $user->getUsername()) $changes['username'] = [$user->getUsername(), $username];
        if ($display_name !== $user->getDisplayName()) $changes['display_name'] = [$user->getDisplayName(), $display_name];
        if ($email !== $user->getEmail()) $changes['email'] = [$user->getEmail(), $email];
        $user_changed = !empty($changes);

        foreach ($model->profile as $field => $value)
        {
            $old = $this->profile->getField($field);
            if ($value !== $old) $changes['profile'][$field] = [$old, $value];
        }

        $model->saved = true;
        $model->email_confirm = null;
        if (empty($changes)) return;

        if ($user_changed)
        {
            $user->setUsername($username);
            $user->setDisplayName($display_name);
            $user->setEmail($email);

            if (!$user->commit())
            {
                $this->failInternal();
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
                $this->failInternal();
                return;
            }
        }

        $this->logEdit($changes);
    }

    private function failInternal(): void
    {
        $model = $this->model();
        $model->saved = false;
        $model->_responseCode = HttpCode::HTTP_INTERNAL_SERVER_ERROR;
        $model->error = UpdateModel::ERROR_INTERNAL;
    }

    private function isEmailDenied(string $email): bool
    {
        foreach (Config::get('email.recipient_denylist_regexp') ?? [] as $pattern)
        {
            if (\preg_match($pattern, $email)) return true;
        }
        return false;
    }

    private function logEdit(array $changes): void
    {
        $model = $this->model();
        $user = $model->user;

        $event = Logger::initEvent(
            EventTypes::USER_EDITED,
            $model->active_user,
            \getenv('REMOTE_ADDR'),
            ['user_id' => $user->getId(), 'changes' => $changes]
        );

        if (!$event->commit()) return;

        $fields = [
            'Edited by' => $model->active_user->getAsMarkdown(),
            'Edited user' => $user->getAsMarkdown(),
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
                fn(string $field): string => Profile::FIELDS[$field][2],
                \array_keys($changes['profile'])
            ));
        }

        Logger::logToDiscord($event, Logger::initDiscordEmbed($event, $user->getURI(), $fields));
    }

    /**
     * Retrieves this controller's model as its concrete type.
     *
     * @return UpdateModel The model.
     * @throws InvalidModelException if the model is not an UpdateModel.
     */
    private function model(): UpdateModel
    {
        if (!$this->model instanceof UpdateModel)
        {
            throw new InvalidModelException($this->model ?? UpdateModel::class);
        }
        return $this->model;
    }
}
