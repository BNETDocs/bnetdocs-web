<?php

namespace BNETDocs\Models;

class PrivacyPolicy extends ActiveUser
{
  public ?string $data_location = null;
  public ?string $email_domain = null;
  public ?string $email_mailbox = null;
  public ?string $organization = null;
}
