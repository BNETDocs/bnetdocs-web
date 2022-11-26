<?php

namespace BNETDocs\Models\User;

use \BNETDocs\Libraries\User;
use \BNETDocs\Libraries\UserProfile;

class Update extends \BNETDocs\Models\ActiveUser
{
  public ?User $user = null;

  public ?string $display_name_1 = null;
  public ?string $display_name_2 = null;
  public ?array $display_name_error = null;
  public int $display_name_max_len = User::MAX_DISPLAY_NAME;

  public ?string $email_1 = null;
  public ?string $email_2 = null;
  public ?array $email_error = null;
  public int $email_max_len = User::MAX_EMAIL;

  public ?string $username = null;
  public ?array $username_error = null;
  public int $username_max_len = User::MAX_USERNAME;

  public ?\BNETDocs\Libraries\UserProfile $profile = null;

  public ?string $biography = null;
  public ?array $biography_error = null;
  public int $biography_max_len = UserProfile::MAX_LEN;

  public ?string $discord_username = null;
  public ?array $discord_username_error = null;
  public int $discord_username_max_len = UserProfile::MAX_LEN;

  public ?string $facebook_username = null;
  public ?array $facebook_username_error = null;
  public int $facebook_username_max_len = UserProfile::MAX_LEN;

  public ?string $github_username = null;
  public ?array $github_username_error = null;
  public int $github_username_max_len = UserProfile::MAX_LEN;

  public ?string $instagram_username = null;
  public ?array $instagram_username_error = null;
  public int $instagram_username_max_len = UserProfile::MAX_LEN;

  public ?string $phone = null;
  public ?array $phone_error = null;
  public int $phone_max_len = UserProfile::MAX_LEN;

  public ?string $reddit_username = null;
  public ?array $reddit_username_error = null;
  public int $reddit_username_max_len = UserProfile::MAX_LEN;

  public ?string $skype_username = null;
  public ?array $skype_username_error = null;
  public int $skype_username_max_len = UserProfile::MAX_LEN;

  public ?string $steam_id = null;
  public ?array $steam_id_error = null;
  public int $steam_id_max_len = UserProfile::MAX_LEN;

  public ?string $twitter_username = null;
  public ?array $twitter_username_error = null;
  public int $twitter_username_max_len = UserProfile::MAX_LEN;

  public ?string $website = null;
  public ?array $website_error = null;
  public int $website_max_len = UserProfile::MAX_LEN;
}
