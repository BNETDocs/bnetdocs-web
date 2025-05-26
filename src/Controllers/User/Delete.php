<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Core\HttpCode;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\EventLog\EventTypes;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Models\User\Delete as DeleteModel;

class Delete extends \BNETDocs\Controllers\Base
{
    public function __construct()
    {
        $this->model = new DeleteModel();
    }

    public function invoke(?array $args): bool
    {
        $this->model->acl_allowed = $this->model->active_user
          && $this->model->active_user->getOption(\BNETDocs\Libraries\User\User::OPTION_ACL_USER_DELETE);

        if (!$this->model->acl_allowed)
        {
            $this->model->_responseCode = HttpCode::HTTP_FORBIDDEN;
            $this->model->error = DeleteModel::ERROR_ACL_NOT_SET;
            return true;
        }
    
        $this->model->form_fields = Router::query();
        $this->model->target_id = $this->model->form_fields['id'] ?? null;

        try
        {
            if (!\is_null($this->model->target_id))
            {
                $this->model->target_user = new \BNETDocs\Libraries\User\User($this->model->target_id);
            }
        }
        catch (\BNETDocs\Exceptions\UserNotFoundException)
        {
            $this->model->target_user = null;
        }
        finally
        {
            if (!$this->model->target_user)
            {
                $this->model->_responseCode = HttpCode::HTTP_NOT_FOUND;
                $this->model->error = DeleteModel::ERROR_NOT_FOUND;
                return true;
            }
        }

        if (Router::requestMethod() == Router::METHOD_POST)
        {
            $this->model->deleted = $this->model->target_user->deallocate();

            if (!$this->model->deleted)
            {
                $this->model->_responseCode = HttpCode::HTTP_INTERNAL_SERVER_ERROR;
                $this->model->error = DeleteModel::ERROR_INTERNAL;
                return true;
            }
            else
            {
                $this->model->error = false;

                $event = Logger::initEvent(
                    EventTypes::USER_DELETED,
                    $this->model->active_user->getId(),
                    getenv('REMOTE_ADDR'),
                    $this->model
                );

                if ($event->commit())
                {
                    $embed = Logger::initDiscordEmbed($event, $this->model->target_user->getURI());
                    Logger::logToDiscord($event, $embed);
                }
            }
        }

        $this->model->_responseCode = HttpCode::HTTP_OK;
        return true;
    }
}
