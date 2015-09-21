<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\UserProfileNotFoundException;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;

class UserProfile {

  protected $biography;
  protected $facebook_username;
  protected $github_username;
  protected $id;
  protected $instagram_username;
  protected $phone;
  protected $skype_username;
  protected $twitter_username;
  protected $website;

  public function __construct($data) {
    if (is_numeric($data)) {
      $this->biography          = null;
      $this->facebook_username  = null;
      $this->github_username    = null;
      $this->id                 = (int) $data;
      $this->instagram_username = null;
      $this->phone              = null;
      $this->skype_username     = null;
      $this->twitter_username   = null;
      $this->website            = null;
      $this->refresh();
    } else if ($data instanceof StdClass) {
      self::normalize($data);
      $this->biography          = $data->biography;
      $this->facebook_username  = $data->facebook_username;
      $this->github_username    = $data->github_username;
      $this->id                 = $data->id;
      $this->instagram_username = $data->instagram_username;
      $this->phone              = $data->phone;
      $this->skype_username     = $data->skype_username;
      $this->twitter_username   = $data->twitter_username;
      $this->website            = $data->website;
    } else {
      throw new InvalidArgumentException("Cannot use data argument");
    }
  }

  public function getBiography() {
    return $this->biography;
  }

  public function getFacebookUsername() {
    return $this->facebook_username;
  }

  public function getGitHubUsername() {
    return $this->github_username;
  }

  public function getId() {
    return $this->id;
  }

  public function getInstagramUsername() {
    return $this->instagram_username;
  }

  public function getPhone() {
    return $this->phone;
  }

  public function getSkypeUsername() {
    return $this->skype_username;
  }

  public function getTwitterUsername() {
    return $this->twitter_username;
  }

  public function getWebsite() {
    return $this->website;
  }

  protected static function normalize(StdClass &$data) {
    $data->id = (int) $data->id;

    if (!is_null($data->biography))
      $data->biography = (string) $data->biography;

    if (!is_null($data->facebook_username))
      $data->facebook_username = (string) $data->facebook_username;

    if (!is_null($data->github_username))
      $data->github_username = (string) $data->github_username;

    if (!is_null($data->instagram_username))
      $data->instagram_username = (string) $data->instagram_username;

    if (!is_null($data->phone))
      $data->phone = (string) $data->phone;

    if (!is_null($data->skype_username))
      $data->skype_username = (string) $data->skype_username;

    if (!is_null($data->twitter_username))
      $data->twitter_username = (string) $data->twitter_username;

    if (!is_null($data->website))
      $data->website = (string) $data->website;

    return true;
  }
  
  public function refresh() {
    $cache_key = "bnetdocs-userprofile-" . $this->id;
    $cache_val = Common::$cache->get($cache_key);
    if ($cache_val !== false) {
      $cache_val = unserialize($cache_val);
      $this->biography          = $cache_val->biography;
      $this->facebook_username  = $cache_val->facebook_username;
      $this->github_username    = $cache_val->github_username;
      $this->instagram_username = $cache_val->instagram_username;
      $this->phone              = $cache_val->phone;
      $this->skype_username     = $cache_val->skype_username;
      $this->twitter_username   = $cache_val->twitter_username;
      $this->website            = $cache_val->website;
      return true;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `biography`,
          `facebook_username`,
          `github_username`,
          `user_id`,
          `instagram_username`,
          `phone`,
          `skype_username`,
          `twitter_username`,
          `website`
        FROM `user_profiles`
        WHERE `user_id` = :id
        LIMIT 1;
      ");
      $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh user profile");
      } else if ($stmt->rowCount() == 0) {
        throw new UserProfileNotFoundException($this->id);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      self::normalize($row);
      $this->biography          = $row->biography;
      $this->facebook_username  = $row->facebook_username;
      $this->github_username    = $row->github_username;
      $this->instagram_username = $row->instagram_username;
      $this->phone              = $row->phone;
      $this->skype_username     = $row->skype_username;
      $this->twitter_username   = $row->twitter_username;
      $this->website            = $row->website;
      Common::$cache->set($cache_key, serialize($row), 300);
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh user profile", $e);
    }
    return false;
  }

}
