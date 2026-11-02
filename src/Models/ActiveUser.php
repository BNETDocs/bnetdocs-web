<?php

namespace BNETDocs\Models;

class ActiveUser extends \BNETDocs\Models\Core\Errorable implements \JsonSerializable
{
  /**
   * The current user that is logged in to the site, or null if not logged in.
   *
   * @var \BNETDocs\Libraries\User\User|null
   */
  public ?\BNETDocs\Libraries\User\User $active_user = null;

  /**
   * Sets the $active_user to that of the Authentication::$user value.
   * Child classes that override __construct() must call parent::__construct().
   */
  public function __construct()
  {
    $this->active_user = &\BNETDocs\Libraries\User\Authentication::$user;
  }

  /**
   * Implements the JSON serialization function from the JsonSerializable interface.
   */
  public function jsonSerialize(): mixed
  {
    return \array_merge(['active_user' => $this->active_user], parent::jsonSerialize());
  }
}
