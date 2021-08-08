<?php namespace BNETDocs\Models\User;
class Update extends \BNETDocs\Models\ActiveUser
{
  const MAX_LEN = 255; // table design: varchar(255)

  public $user;

  public $display_name_1;
  public $display_name_2;
  public $display_name_error;

  public $email_1;
  public $email_2;
  public $email_error;

  public $username;
  public $username_error;
  public $username_max_len;

  public $profile;

  public $biography;
  public $biography_error;
  public $biography_max_len = self::MAX_LEN;

  public $discord_username;
  public $discord_username_error;
  public $discord_username_max_len = self::MAX_LEN;

  public $facebook_username;
  public $facebook_username_error;
  public $facebook_username_max_len = self::MAX_LEN;

  public $github_username;
  public $github_username_error;
  public $github_username_max_len = self::MAX_LEN;

  public $instagram_username;
  public $instagram_username_error;
  public $instagram_username_max_len = self::MAX_LEN;

  public $phone;
  public $phone_error;
  public $phone_max_len = self::MAX_LEN;

  public $reddit_username;
  public $reddit_username_error;
  public $reddit_username_max_len = self::MAX_LEN;

  public $skype_username;
  public $skype_username_error;
  public $skype_username_max_len = self::MAX_LEN;

  public $steam_id;
  public $steam_id_error;
  public $steam_id_max_len = self::MAX_LEN;

  public $twitter_username;
  public $twitter_username_error;
  public $twitter_username_max_len = self::MAX_LEN;

  public $website;
  public $website_error;
  public $website_max_len = self::MAX_LEN;
}
