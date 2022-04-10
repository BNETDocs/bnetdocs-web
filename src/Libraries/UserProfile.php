<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\UserProfileNotFoundException;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \InvalidArgumentException;
use \PDO;
use \StdClass;

class UserProfile
{
  protected $biography;
  protected $discord_username;
  protected $facebook_username;
  protected $github_username;
  protected $instagram_username;
  protected $phone;
  protected $reddit_username;
  protected $skype_username;
  protected $steam_id;
  protected $twitter_username;
  protected $user_id;
  protected $website;

  public function __construct($data)
  {
    if (is_numeric($data))
    {
      $this->biography          = null;
      $this->discord_username   = null;
      $this->facebook_username  = null;
      $this->github_username    = null;
      $this->instagram_username = null;
      $this->phone              = null;
      $this->reddit_username    = null;
      $this->skype_username     = null;
      $this->steam_id           = null;
      $this->twitter_username   = null;
      $this->user_id            = (int) $data;
      $this->website            = null;
      $this->refresh();
    }
    else if ($data instanceof StdClass)
    {
      self::normalize($data);
      $this->biography          = $data->biography;
      $this->discord_username   = $data->discord_username;
      $this->facebook_username  = $data->facebook_username;
      $this->github_username    = $data->github_username;
      $this->instagram_username = $data->instagram_username;
      $this->phone              = $data->phone;
      $this->reddit_username    = $data->reddit_username;
      $this->skype_username     = $data->skype_username;
      $this->steam_id           = $data->steam_id;
      $this->twitter_username   = $data->twitter_username;
      $this->user_id            = $data->user_id;
      $this->website            = $data->website;
    }
    else
    {
      throw new InvalidArgumentException('Cannot use data argument');
    }
  }

  public function getBiography()
  {
    return $this->biography;
  }

  public function getDiscordUsername()
  {
    return $this->discord_username;
  }

  public function getFacebookURI()
  {
    return 'https://www.facebook.com/' . rawurlencode($this->getFacebookUsername());
  }

  public function getFacebookUsername()
  {
    return $this->facebook_username;
  }

  public function getGitHubURI()
  {
    return 'https://github.com/' . rawurlencode($this->getGitHubUsername());
  }

  public function getGitHubUsername()
  {
    return $this->github_username;
  }

  public function getInstagramURI()
  {
    return 'https://instagram.com/' . rawurlencode($this->getInstagramUsername());
  }

  public function getInstagramUsername()
  {
    return $this->instagram_username;
  }

  public function getPhone()
  {
    return $this->phone;
  }

  public function getPhoneURI()
  {
    return 'tel://' . $this->getPhone();
  }

  public function getRedditURI()
  {
    return 'https://www.reddit.com/user/' . rawurlencode($this->getRedditUsername());
  }

  public function getRedditUsername()
  {
    return $this->reddit_username;
  }

  public function getSkypeURI()
  {
    return 'skype:' . rawurlencode($this->getSkypeUsername()) . '?chat';
  }

  public function getSkypeUsername()
  {
    return $this->skype_username;
  }

  public function getSteamId()
  {
    return $this->steam_id;
  }

  public function getSteamURI()
  {
    $steam = $this->getSteamId();
    return sprintf('https://steamcommunity.com/%s/%s',
      (is_numeric($steam) ? 'profiles' : 'id'), rawurlencode($steam)
    );
  }

  public function getTwitterURI()
  {
    return 'https://twitter.com/' . rawurlencode($this->getTwitterUsername());
  }

  public function getTwitterUsername()
  {
    return $this->twitter_username;
  }

  public function getUser()
  {
    return new User($this->user_id);
  }

  public function getUserId()
  {
    return $this->user_id;
  }

  public function getWebsite($clean = true)
  {
    if (empty($this->website) || !$clean)
    {
      return $this->website;
    }

    return $this->getWebsiteURI();
  }

  public function getWebsiteURI()
  {
    if (!is_string($this->website)) return $this->website;
    $value = strtolower($this->website);

    // append 'http://' if value does not begin with 'http://' or 'https://'
    return ((substr($value, 0, 7) == 'http://' || substr($value, 0, 8) == 'https://') ? '' : 'http://') . $value;
  }

  protected static function normalize(StdClass &$data)
  {
    $data->user_id  = (int) $data->user_id;

    if (!is_null($data->biography)) $data->biography = (string) $data->biography;
    if (!is_null($data->discord_username)) $data->discord_username = (string) $data->discord_username;
    if (!is_null($data->facebook_username)) $data->facebook_username = (string) $data->facebook_username;
    if (!is_null($data->github_username)) $data->github_username = (string) $data->github_username;
    if (!is_null($data->instagram_username)) $data->instagram_username = (string) $data->instagram_username;
    if (!is_null($data->phone)) $data->phone = (string) $data->phone;
    if (!is_null($data->reddit_username)) $data->reddit_username = (string) $data->reddit_username;
    if (!is_null($data->skype_username)) $data->skype_username = (string) $data->skype_username;
    if (!is_null($data->twitter_username)) $data->twitter_username = (string) $data->twitter_username;
    if (!is_null($data->steam_id)) $data->steam_id = (string) $data->steam_id;
    if (!is_null($data->website)) $data->website = (string) $data->website;

    return true;
  }

  public function refresh()
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $stmt = Common::$database->prepare(
     'SELECT
        `biography`,
        `discord_username`,
        `facebook_username`,
        `github_username`,
        `instagram_username`,
        `phone`,
        `reddit_username`,
        `skype_username`,
        `steam_id`,
        `twitter_username`,
        `user_id`,
        `website`
      FROM `user_profiles`
      WHERE `user_id` = :id
      LIMIT 1;'
    );

    $stmt->bindParam(':id', $this->user_id, PDO::PARAM_INT);

    $r = $stmt->execute();
    if (!$r)
    {
      throw new QueryException('Cannot refresh user profile');
    }
    else if ($stmt->rowCount() == 0)
    {
      throw new UserProfileNotFoundException($this->user_id);
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);
    $stmt->closeCursor();

    self::normalize($row);
    $this->biography          = $row->biography;
    $this->discord_username   = $row->discord_username;
    $this->facebook_username  = $row->facebook_username;
    $this->github_username    = $row->github_username;
    $this->instagram_username = $row->instagram_username;
    $this->phone              = $row->phone;
    $this->reddit_username    = $row->reddit_username;
    $this->skype_username     = $row->skype_username;
    $this->steam_id           = $row->steam_id;
    $this->twitter_username   = $row->twitter_username;
    $this->website            = $row->website;

    return true;
  }

  public function save()
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $stmt = Common::$database->prepare(
     'INSERT INTO `user_profiles` (
        `biography`,
        `discord_username`,
        `facebook_username`,
        `github_username`,
        `instagram_username`,
        `phone`,
        `reddit_username`,
        `skype_username`,
        `steam_id`,
        `twitter_username`,
        `user_id`,
        `website`
      ) VALUES (
        :bio,
        :discord,
        :fb,
        :github,
        :ig,
        :ph,
        :reddit,
        :skype,
        :steam,
        :twitter,
        :user_id,
        :website
      ) ON DUPLICATE KEY UPDATE
        `biography` = :bio,
        `discord_username` = :discord,
        `facebook_username` = :fb,
        `github_username` = :github,
        `instagram_username` = :ig,
        `phone` = :ph,
        `reddit_username` = :reddit,
        `skype_username` = :skype,
        `steam_id` = :steam,
        `twitter_username` = :twitter,
        `user_id` = :user_id,
        `website` = :website
    ;');

    $stmt->bindParam(':bio', $this->biography, (
      is_null($this->biography) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $stmt->bindParam(':discord', $this->discord_username, (
      is_null($this->discord_username) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $stmt->bindParam(':fb', $this->facebook_username, (
      is_null($this->facebook_username) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $stmt->bindParam(':github', $this->github_username, (
      is_null($this->github_username) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $stmt->bindParam(':ig', $this->instagram_username, (
      is_null($this->instagram_username) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $stmt->bindParam(':ph', $this->phone, (
      is_null($this->phone) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $stmt->bindParam(':reddit', $this->reddit_username, (
      is_null($this->reddit_username) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $stmt->bindParam(':skype', $this->skype_username, (
      is_null($this->skype_username) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $stmt->bindParam(':steam', $this->steam_id, (
      is_null($this->steam_id) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $stmt->bindParam(':twitter', $this->twitter_username, (
      is_null($this->twitter_username) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);

    $stmt->bindParam(':website', $this->website, (
      is_null($this->website) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    if (!$stmt->execute())
    {
      throw new QueryException('Cannot save user profile');
    }

    $stmt->closeCursor();
    return true;
  }

  public function setBiography(?string $value)
  {
    $this->biography = $value;
  }

  public function setDiscordUsername(?string $value)
  {
    $this->discord_username = $value;
  }

  public function setFacebookUsername(?string $value)
  {
    $this->facebook_username = $value;
  }

  public function setGitHubUsername(?string $value)
  {
    $this->github_username = $value;
  }

  public function setInstagramUsername(?string $value)
  {
    $this->instagram_username = $value;
  }

  public function setPhone(?string $value)
  {
    $this->phone = $value;
  }

  public function setRedditUsername(?string $value)
  {
    $this->reddit_username = $value;
  }

  public function setSkypeUsername(?string $value)
  {
    $this->skype_username = $value;
  }

  public function setSteamId(?string $value)
  {
    $this->steam_id = $value;
  }

  public function setTwitterUsername(?string $value)
  {
    $this->twitter_username = $value;
  }

  public function setWebsite(?string $value)
  {
    $this->website = $value;
  }
}
