<?php

namespace BNETDocs\Exceptions;

class UserProfileNotFoundException extends DatabaseObjectNotFoundException
{
  public function __construct(\BNETDocs\Libraries\UserProfile|int $value, \Throwable $previous = null)
  {
    $v = is_int($value) ? $value : $value->getUserId();
    parent::__construct(\sprintf('User Profile not found: %d', $v), 0, $previous);
    \BNETDocs\Libraries\Logger::logMetric('user_profile_user_id', $v);
  }
}
