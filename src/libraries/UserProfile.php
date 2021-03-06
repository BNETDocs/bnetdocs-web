<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\UserProfileNotFoundException;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Database;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;

class UserProfile {

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

  public function __construct($data) {
    if (is_numeric($data)) {
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
    } else if ($data instanceof StdClass) {
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
    } else {
      throw new InvalidArgumentException("Cannot use data argument");
    }
  }

  public function getBiography() {
    return $this->biography;
  }

  public function getDiscordUsername() {
    return $this->discord_username;
  }

  public function getFacebookURI() {
    return "https://www.facebook.com/" . $this->getFacebookUsername();
  }

  public function getFacebookUsername() {
    return $this->facebook_username;
  }

  public function getGitHubURI() {
    return "https://github.com/" . $this->getGitHubUsername();
  }

  public function getGitHubUsername() {
    return $this->github_username;
  }

  public function getInstagramURI() {
    return "https://instagram.com/" . $this->getInstagramUsername();
  }

  public function getInstagramUsername() {
    return $this->instagram_username;
  }

  public function getPhone() {
    return $this->phone;
  }

  public function getPhoneURI() {
    return "tel://" . $this->getPhone();
  }

  public function getRedditURI() {
    return "https://www.reddit.com/user/" . $this->getRedditUsername();
  }

  public function getRedditUsername() {
    return $this->reddit_username;
  }

  public function getSkypeURI() {
    return "skype:" . $this->getSkypeUsername() . "?chat";
  }

  public function getSkypeUsername() {
    return $this->skype_username;
  }

  public function getSteamId() {
    return $this->steam_id;
  }

  public function getSteamURI() {
    $steam = $this->getSteamId();
    if (is_numeric($steam)) {
      return "https://steamcommunity.com/profiles/" . $steam;
    } else {
      return "https://steamcommunity.com/id/" . $steam;
    }
  }

  public function getTwitterURI() {
    return "https://twitter.com/" . $this->getTwitterUsername();
  }

  public function getTwitterUsername() {
    return $this->twitter_username;
  }

  public function getUser() {
    return new User($this->user_id);
  }

  public function getUserId() {
    return $this->user_id;
  }

  public function getWebsite($clean = true) {
    if (empty($this->website) || !$clean) {
      return $this->website;
    }
    $value = strtolower($this->website);
    if (substr($value, 0, 7) == "http://") {
      return substr($value, 7);
    } else if (substr($value, 0, 8) == "https://") {
      return substr($value, 8);
    } else {
      return "http://" . $value;
    }
  }

  public function getWebsiteURI() {
    if (!is_string($this->website)) return $this->website;
    $value = strtolower($this->website);
    if (substr($value, 0, 7) == "http://"
      || substr($value, 0, 8) == "https://") {
      return $value;
    } else {
      return "http://" . $value;
    }
  }

  protected static function normalize(StdClass &$data) {
    $data->user_id  = (int) $data->user_id;

    if (!is_null($data->biography))
      $data->biography = (string) $data->biography;

    if (!is_null($data->discord_username))
      $data->discord_username = (string) $data->discord_username;

    if (!is_null($data->facebook_username))
      $data->facebook_username = (string) $data->facebook_username;

    if (!is_null($data->github_username))
      $data->github_username = (string) $data->github_username;

    if (!is_null($data->instagram_username))
      $data->instagram_username = (string) $data->instagram_username;

    if (!is_null($data->phone))
      $data->phone = (string) $data->phone;

    if (!is_null($data->reddit_username))
      $data->reddit_username = (string) $data->reddit_username;

    if (!is_null($data->skype_username))
      $data->skype_username = (string) $data->skype_username;

    if (!is_null($data->twitter_username))
      $data->twitter_username = (string) $data->twitter_username;

    if (!is_null($data->steam_id))
      $data->steam_id = (string) $data->steam_id;

    if (!is_null($data->website))
      $data->website = (string) $data->website;

    return true;
  }

  public function refresh() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
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
        LIMIT 1;
      ");
      $stmt->bindParam(":id", $this->user_id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh user profile");
      } else if ($stmt->rowCount() == 0) {
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
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh user profile", $e);
    }
    return false;
  }

  public function save() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare('
        INSERT INTO `user_profiles` (
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

      if (is_null($this->biography)) {
        $stmt->bindParam(':bio', $this->biography, PDO::PARAM_NULL);
      } else {
        $stmt->bindParam(':bio', $this->biography, PDO::PARAM_STR);
      }

      if (is_null($this->discord_username)) {
        $stmt->bindParam(':discord', $this->discord_username, PDO::PARAM_NULL);
      } else {
        $stmt->bindParam(':discord', $this->discord_username, PDO::PARAM_STR);
      }

      if (is_null($this->facebook_username)) {
        $stmt->bindParam(':fb', $this->facebook_username, PDO::PARAM_NULL);
      } else {
        $stmt->bindParam(':fb', $this->facebook_username, PDO::PARAM_STR);
      }

      if (is_null($this->github_username)) {
        $stmt->bindParam(':github', $this->github_username, PDO::PARAM_NULL);
      } else {
        $stmt->bindParam(':github', $this->github_username, PDO::PARAM_STR);
      }

      if (is_null($this->instagram_username)) {
        $stmt->bindParam(':ig', $this->instagram_username, PDO::PARAM_NULL);
      } else {
        $stmt->bindParam(':ig', $this->instagram_username, PDO::PARAM_STR);
      }

      if (is_null($this->phone)) {
        $stmt->bindParam(':ph', $this->phone, PDO::PARAM_NULL);
      } else {
        $stmt->bindParam(':ph', $this->phone, PDO::PARAM_STR);
      }

      if (is_null($this->reddit_username)) {
        $stmt->bindParam(':reddit', $this->reddit_username, PDO::PARAM_NULL);
      } else {
        $stmt->bindParam(':reddit', $this->reddit_username, PDO::PARAM_STR);
      }

      if (is_null($this->skype_username)) {
        $stmt->bindParam(':skype', $this->skype_username, PDO::PARAM_NULL);
      } else {
        $stmt->bindParam(':skype', $this->skype_username, PDO::PARAM_STR);
      }

      if (is_null($this->steam_id)) {
        $stmt->bindParam(':steam', $this->steam_id, PDO::PARAM_NULL);
      } else {
        $stmt->bindParam(':steam', $this->steam_id, PDO::PARAM_STR);
      }

      if (is_null($this->twitter_username)) {
        $stmt->bindParam(':twitter', $this->twitter_username, PDO::PARAM_NULL);
      } else {
        $stmt->bindParam(':twitter', $this->twitter_username, PDO::PARAM_STR);
      }

      $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);

      if (is_null($this->website)) {
        $stmt->bindParam(':website', $this->website, PDO::PARAM_NULL);
      } else {
        $stmt->bindParam(':website', $this->website, PDO::PARAM_STR);
      }

      if (!$stmt->execute()) {
        throw new QueryException('Cannot save user profile');
      }

      $stmt->closeCursor();
      return true;
    } catch (PDOException $e) {
      throw new QueryException('Cannot save user profile', $e);
    }
    return false;
  }

  public function setBiography($value) {
    $this->biography = $value;
  }

  public function setDiscordUsername($value) {
    $this->discord_username = $value;
  }

  public function setFacebookUsername($value) {
    $this->facebook_username = $value;
  }

  public function setGitHubUsername($value) {
    $this->github_username = $value;
  }

  public function setInstagramUsername($value) {
    $this->instagram_username = $value;
  }

  public function setPhone($value) {
    $this->phone = $value;
  }

  public function setRedditUsername($value) {
    $this->reddit_username = $value;
  }

  public function setSkypeUsername($value) {
    $this->skype_username = $value;
  }

  public function setSteamId($value) {
    $this->steam_id = $value;
  }

  public function setTwitterUsername($value) {
    $this->twitter_username = $value;
  }

  public function setWebsite($value) {
    $this->website = $value;
  }

}
