<?php

namespace BNETDocs\Models;

class ActiveUser extends Errorable implements \JsonSerializable
{
  /**
   * The current user that is logged in to the site, or null if not logged in.
   *
   * @var \BNETDocs\Libraries\User|null
   */
  public ?\BNETDocs\Libraries\User $active_user = null;

  /**
   * When constructed, sets the $active_user to that of the Authentication::$user value.
   * Child classes that override __construct() must call parent::__construct().
   */
  public function __construct()
  {
    $this->active_user = &\BNETDocs\Libraries\Authentication::$user;
  }

  /**
   * Implements the JSON serialization function from the JsonSerializable interface.
   */
  public function jsonSerialize() : mixed
  {
    return \array_merge(['active_user' => $this->active_user], parent::jsonSerialize());
  }
}
