<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\DateTimeImmutable;
use \DateTimeZone;

class Legal extends Base
{
  public const LICENSE_FILE = '../LICENSE.txt';

  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Legal();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args): bool
  {
    $privacy_contact = &\CarlBennett\MVC\Libraries\Common::$config->bnetdocs->privacy->contact;
    $this->model->email_domain = $privacy_contact->email_domain;
    $this->model->email_mailbox = $privacy_contact->email_mailbox;

    $this->model->license = \file_get_contents(self::LICENSE_FILE);
    $this->model->license_version = \BNETDocs\Libraries\VersionInfo::$version['bnetdocs'][3] ?? null;

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

    $this->model->_responseCode = 200;
    return true;
  }
}
