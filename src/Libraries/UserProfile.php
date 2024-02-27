<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\User;
use \StdClass;
use \UnexpectedValueException;

class UserProfile implements \BNETDocs\Interfaces\DatabaseObject, \JsonSerializable
{
  public const MAX_LEN = 0xFF; // table design: varchar(255)
  public const MAX_USER_ID = User::MAX_ID;

  protected ?string $biography;
  protected ?string $discord_username;
  protected ?string $facebook_username;
  protected ?string $github_username;
  protected ?string $instagram_username;
  protected ?string $phone;
  protected ?string $reddit_username;
  protected ?string $skype_username;
  protected ?string $steam_id;
  protected ?string $twitter_username;
  protected ?int $user_id;
  protected ?string $website;

  /**
   * Constructs a UserProfile object from properties, or a user id to lookup.
   *
   * @param StdClass|integer|null $value Object properties or user id, or null for a new profile.
   */
  public function __construct(StdClass|int|null $value)
  {
    if ($value instanceof StdClass)
    {
      $this->allocateObject($value);
    }
    else
    {
      $this->setUserId($value);
      if (!$this->allocate()) throw new \BNETDocs\Exceptions\UserProfileNotFoundException($this);
    }
  }

  public function allocate(): bool
  {
    $user_id = $this->getUserId();
    if (is_null($user_id)) return true;

    $this->setBiography(null);
    $this->setDiscordUsername(null);
    $this->setFacebookUsername(null);
    $this->setGitHubUsername(null);
    $this->setInstagramUsername(null);
    $this->setPhone(null);
    $this->setRedditUsername(null);
    $this->setSkypeUsername(null);
    $this->setSteamId(null);
    $this->setTwitterUsername(null);
    $this->setWebsite(null);

    $q = Database::instance()->prepare('
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
      FROM `user_profiles` WHERE `user_id` = ? LIMIT 1;
    ');
    if (!$q || !$q->execute([$user_id]) || $q->rowCount() == 0) return false;
    $this->allocateObject($q->fetchObject());
    $q->closeCursor();
    return true;
  }

  protected function allocateObject(StdClass $value): void
  {
    $this->setBiography($value->biography);
    $this->setDiscordUsername($value->discord_username);
    $this->setFacebookUsername($value->facebook_username);
    $this->setGitHubUsername($value->github_username);
    $this->setInstagramUsername($value->instagram_username);
    $this->setPhone($value->phone);
    $this->setRedditUsername($value->reddit_username);
    $this->setSkypeUsername($value->skype_username);
    $this->setSteamId($value->steam_id);
    $this->setTwitterUsername($value->twitter_username);
    $this->setUserId($value->user_id);
    $this->setWebsite($value->website);
  }

  public function commit(): bool
  {
    $user_id = $this->getUserId();
    if (is_null($user_id)) throw new UnexpectedValueException('user id cannot be null');

    $q = Database::instance()->prepare('
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
        :gh,
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
        `github_username` = :gh,
        `instagram_username` = :ig,
        `phone` = :ph,
        `reddit_username` = :reddit,
        `skype_username` = :skype,
        `steam_id` = :steam,
        `twitter_username` = :twitter,
        `user_id` = :user_id,
        `website` = :website;
    ');

    $p = [
      ':bio' => $this->getBiography(),
      ':discord' => $this->getDiscordUsername(),
      ':fb' => $this->getFacebookUsername(),
      ':gh' => $this->getGitHubUsername(),
      ':ig' => $this->getInstagramUsername(),
      ':ph' => $this->getPhone(),
      ':reddit' => $this->getRedditUsername(),
      ':skype' => $this->getSkypeUsername(),
      ':steam' => $this->getSteamId(),
      ':twitter' => $this->getTwitterUsername(),
      ':user_id' => $user_id,
      ':website' => $this->getWebsite(false),
    ];

    if (!$q || !$q->execute($p)) return false;
    $q->closeCursor();
    return true;
  }

  /**
   * Deallocates the properties of this object from the database.
   *
   * @return boolean Whether the operation was successful.
   */
  public function deallocate(): bool
  {
    $id = $this->getUserId();
    if (is_null($id)) return false;
    $q = Database::instance()->prepare('DELETE FROM `user_profiles` WHERE `user_id` = ? LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { $q->closeCursor(); }
  }

  public function getBiography(): ?string
  {
    return $this->biography;
  }

  public function getDiscordURI(): ?string
  {
    $value = $this->getDiscordUsername();
    return is_null($value) ? $value : sprintf('https://discordapp.com/users/%s', rawurlencode($value));
  }

  public function getDiscordUsername(): ?string
  {
    return $this->discord_username;
  }

  public function getFacebookURI(): ?string
  {
    $value = $this->getFacebookUsername();
    return is_null($value) ? $value : sprintf('https://www.facebook.com/%s', rawurlencode($value));
  }

  public function getFacebookUsername(): ?string
  {
    return $this->facebook_username;
  }

  public function getGitHubURI(): ?string
  {
    $value = $this->getGitHubUsername();
    return is_null($value) ? $value : sprintf('https://github.com/%s', rawurlencode($value));
  }

  public function getGitHubUsername(): ?string
  {
    return $this->github_username;
  }

  public function getInstagramURI(): ?string
  {
    $value = $this->getInstagramUsername();
    return is_null($value) ? $value : sprintf('https://instagram.com/%s', rawurlencode($value));
  }

  public function getInstagramUsername(): ?string
  {
    return $this->instagram_username;
  }

  public function getPhone(): ?string
  {
    return $this->phone;
  }

  public function getPhoneURI(): ?string
  {
    $value = $this->getPhone();
    return is_null($value) ? $value : sprintf('tel://%s', rawurlencode($value));
  }

  public function getRedditURI(): ?string
  {
    $value = $this->getRedditUsername();
    return is_null($value) ? $value : sprintf('https://reddit.com/u/%s', rawurlencode($value));
  }

  public function getRedditUsername(): ?string
  {
    return $this->reddit_username;
  }

  public function getSkypeURI(): ?string
  {
    $value = $this->getSkypeUsername();
    return is_null($value) ? $value : sprintf('skype:%s?chat', rawurlencode($value));
  }

  public function getSkypeUsername(): ?string
  {
    return $this->skype_username;
  }

  public function getSteamId(): ?string
  {
    return $this->steam_id;
  }

  public function getSteamURI(): ?string
  {
    $value = $this->getSteamId();
    return is_null($value) ? $value : sprintf('https://steamcommunity.com/%s/%s',
      (is_numeric($value) ? 'profiles' : 'id'), rawurlencode($value)
    );
  }

  public function getTwitterURI(): ?string
  {
    $value = $this->getTwitterUsername();
    return is_null($value) ? $value : sprintf('https://twitter.com/%s', rawurlencode($value));
  }

  public function getTwitterUsername(): ?string
  {
    return $this->twitter_username;
  }

  public function getUser(): ?User
  {
    return is_null($this->user_id) ? null : new User($this->user_id);
  }

  public function getUserId(): int
  {
    return $this->user_id;
  }

  public function getWebsite($clean = true): ?string
  {
    return (empty($this->website) || !$clean) ? $this->website : $this->getWebsiteURI();
  }

  public function getWebsiteURI(): ?string
  {
    if (empty($this->website)) return $this->website;
    $value = strtolower($this->website);

    // append 'http://' if value does not begin with 'http://' or 'https://'
    return ((substr($value, 0, 7) == 'http://' || substr($value, 0, 8) == 'https://') ? '' : 'http://') . $value;
  }

  public function jsonSerialize(): mixed
  {
    return [
      'biography' => $this->getBiography(),
      'discord' => $this->getDiscordURI(),
      'facebook' => $this->getFacebookURI(),
      'github' => $this->getGitHubURI(),
      'instagram' => $this->getInstagramURI(),
      'phone' => $this->getPhone(),
      'reddit' => $this->getRedditURI(),
      'skype' => $this->getSkypeURI(),
      'steam_id' => $this->getSteamId(),
      'twitter' => $this->getTwitterURI(),
      'user_id' => $this->getUserId(),
      'website' => $this->getWebsiteURI(),
    ];
  }

  public function setBiography(?string $value): void
  {
    if (!is_null($value) && strlen($value) > self::MAX_LEN)
    {
      throw new UnexpectedValueException(sprintf(
        'value must be null or a string between 0-%d characters', self::MAX_LEN
      ));
    }

    $this->biography = $value;
  }

  public function setDiscordUsername(?string $value): void
  {
    if (!is_null($value) && strlen($value) > self::MAX_LEN)
    {
      throw new UnexpectedValueException(sprintf(
        'value must be null or a string between 0-%d characters', self::MAX_LEN
      ));
    }

    $this->discord_username = $value;
  }

  public function setFacebookUsername(?string $value): void
  {
    if (!is_null($value) && strlen($value) > self::MAX_LEN)
    {
      throw new UnexpectedValueException(sprintf(
        'value must be null or a string between 0-%d characters', self::MAX_LEN
      ));
    }

    $this->facebook_username = $value;
  }

  public function setGitHubUsername(?string $value): void
  {
    if (!is_null($value) && strlen($value) > self::MAX_LEN)
    {
      throw new UnexpectedValueException(sprintf(
        'value must be null or a string between 0-%d characters', self::MAX_LEN
      ));
    }

    $this->github_username = $value;
  }

  public function setInstagramUsername(?string $value): void
  {
    if (!is_null($value) && strlen($value) > self::MAX_LEN)
    {
      throw new UnexpectedValueException(sprintf(
        'value must be null or a string between 0-%d characters', self::MAX_LEN
      ));
    }

    $this->instagram_username = $value;
  }

  public function setPhone(?string $value): void
  {
    if (!is_null($value) && strlen($value) > self::MAX_LEN)
    {
      throw new UnexpectedValueException(sprintf(
        'value must be null or a string between 0-%d characters', self::MAX_LEN
      ));
    }

    $this->phone = $value;
  }

  public function setRedditUsername(?string $value): void
  {
    if (!is_null($value) && strlen($value) > self::MAX_LEN)
    {
      throw new UnexpectedValueException(sprintf(
        'value must be null or a string between 0-%d characters', self::MAX_LEN
      ));
    }

    $this->reddit_username = $value;
  }

  public function setSkypeUsername(?string $value): void
  {
    if (!is_null($value) && strlen($value) > self::MAX_LEN)
    {
      throw new UnexpectedValueException(sprintf(
        'value must be null or a string between 0-%d characters', self::MAX_LEN
      ));
    }

    $this->skype_username = $value;
  }

  public function setSteamId(?string $value): void
  {
    if (!is_null($value) && strlen($value) > self::MAX_LEN)
    {
      throw new UnexpectedValueException(sprintf(
        'value must be null or a string between 0-%d characters', self::MAX_LEN
      ));
    }

    $this->steam_id = $value;
  }

  public function setTwitterUsername(?string $value): void
  {
    if (!is_null($value) && strlen($value) > self::MAX_LEN)
    {
      throw new UnexpectedValueException(sprintf(
        'value must be null or a string between 0-%d characters', self::MAX_LEN
      ));
    }

    $this->twitter_username = $value;
  }

  public function setUserId(?int $value): void
  {
    if (!is_null($value) && ($value < 0 || $value > self::MAX_USER_ID))
    {
      throw new UnexpectedValueException(sprintf(
        'value must be null or an integer between 0-%d', self::MAX_USER_ID
      ));
    }

    $this->user_id = $value;
  }

  public function setWebsite(?string $value): void
  {
    if (!is_null($value) && strlen($value) > self::MAX_LEN)
    {
      throw new UnexpectedValueException(sprintf(
        'value must be null or a string between 0-%d characters', self::MAX_LEN
      ));
    }

    $this->website = $value;
  }
}
