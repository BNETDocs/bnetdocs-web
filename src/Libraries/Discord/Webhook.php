<?php

namespace BNETDocs\Libraries\Discord;

use \BNETDocs\Libraries\Discord\Embed;
use \LogicException;
use \OverflowException;
use \SplObjectStorage;

// <https://discordapp.com/developers/docs/resources/webhook#execute-webhook>

class Webhook implements \JsonSerializable
{
  public const MAX_EMBEDS = 10;

  protected string $avatar_url;
  protected string $content;
  protected SplObjectStorage $embeds;
  protected string $file;
  protected bool $tts;
  protected string $username;
  protected bool $wait;
  protected string $webhook_url;

  public function __construct(string $webhook_url)
  {
    $this->avatar_url  = '';
    $this->content     = '';
    $this->embeds      = new SplObjectStorage();
    $this->file        = '';
    $this->tts         = false;
    $this->username    = '';
    $this->wait        = false;
    $this->webhook_url = $webhook_url;
  }

  public function addEmbed(Embed $value): void
  {
    if (!empty($this->content) || !empty($this->file))
      throw new LogicException(
        'Discord forbids adding embeds with content or file contents'
      );

    if ($this->embeds->count() >= self::MAX_EMBEDS)
      throw new OverflowException(sprintf(
        'Discord forbids adding more than %d embeds', self::MAX_EMBEDS
      ));

    $this->embeds->attach($value);
  }

  public function embedCount(): int
  {
    return $this->embeds->count();
  }

  public function hasEmbed(Embed $value): bool
  {
    return $this->embeds->contains($value);
  }

  public function jsonSerialize(): mixed
  {
    $r = [
      'avatar_url' => $this->avatar_url,
      'content' => $this->content,
      'embeds' => [],
      'file' => $this->file,
      'tts' => $this->tts,
      'username' => $this->username,
      'wait' => $this->wait,
    ];
    foreach ($this->embeds as $v) $r['embeds'][] = $v;
    foreach ($r as $k => $v) if (empty($v)) unset($r[$k]);
    return $r;
  }

  public function removeAllEmbeds(): void
  {
    $this->embeds = new SplObjectStorage();
  }

  public function send(int $connect_timeout = 5, int $max_redirects = 10): \StdClass
  {
    return \CarlBennett\MVC\Libraries\Common::curlRequest(
      $this->webhook_url,
      json_encode($this),
      'application/json',
      $connect_timeout,
      $max_redirects
    );
  }

  public function removeEmbed(Embed $embed_object): void
  {
    $this->embeds->detach($embed_object);
  }

  public function setAvatarUrl(string $value): void
  {
    $this->avatar_url = $value;
  }

  public function setContent(string $value): void
  {
    if (!empty($this->file) || $this->embeds->count() > 0)
    {
      throw new LogicException(
        'Discord forbids adding content with embeds or file contents'
      );
    }

    $this->content = $value;
  }

  public function setFileContents(string $value): void
  {
    if (!empty($this->content) || $this->embeds->count() > 0)
    {
      throw new LogicException(
        'Discord forbids adding file contents with content or embeds'
      );
    }

    $this->file = $value;
  }

  public function setTTS(bool $value): void
  {
    $this->tts = $value;
  }

  public function setUsername(string $value): void
  {
    $this->username = $value;
  }

  public function setWait(bool $value): void
  {
    $this->wait = $value;
  }

  public function setWebhookUrl(string $value): void
  {
    if (empty($webhook_url)) throw new \LengthException('Webhook url must not be empty');
    $this->webhook_url = $value;
  }
}
