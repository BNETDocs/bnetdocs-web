<?php

namespace BNETDocs\Controllers\Community;

use \BNETDocs\Libraries\Core\Config;
use \BNETDocs\Libraries\Core\DateTimeImmutable;
use \DateTimeZone;

class Legal extends \BNETDocs\Controllers\Base
{
    public const LICENSE_FILE = __DIR__ . '/../../../LICENSE.txt';

    /**
     * Constructs a Controller, typically to initialize properties.
     */
    public function __construct()
    {
        $this->model = new \BNETDocs\Models\Community\Legal();
    }

    /**
     * Invoked by the Router class to handle the request.
     *
     * @param array|null $args The optional route arguments and any captured URI arguments.
     * @return boolean Whether the Router should invoke the configured View.
     */
    public function invoke(?array $args): bool
    {
        $this->model->email_domain = Config::get('bnetdocs.privacy.contact.email_domain');
        $this->model->email_mailbox = Config::get('bnetdocs.privacy.contact.email_mailbox');

        $this->model->license = \file_get_contents(self::LICENSE_FILE);
        $this->model->license_version = \BNETDocs\Libraries\Core\VersionInfo::get()['bnetdocs'][3] ?? null;

        if (!\is_null($this->model->license_version))
        {
            $this->model->license_version = \explode(' ', $this->model->license_version);
            $this->model->license_version[1] = new DateTimeImmutable(
                $this->model->license_version[1], new DateTimeZone('Etc/UTC')
            );
        }
        else
        {
            $this->model->license_version = [];
            $this->model->license_version[0] = null;
            $this->model->license_version[1] = new DateTimeImmutable(
                '@' . \filemtime(self::LICENSE_FILE), new DateTimeZone('Etc/UTC')
            );
        }

        $this->model->_responseCode = \BNETDocs\Libraries\Core\HttpCode::HTTP_OK;
        return true;
    }
}
