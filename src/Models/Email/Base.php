<?php

namespace BNETDocs\Models\Email;

abstract class Base implements \BNETDocs\Interfaces\Model
{
  public ?\BNETDocs\Libraries\User $active_user;
  public $mail;
}
