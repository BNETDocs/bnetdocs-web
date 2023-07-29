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

  public function send(bool $wait = false, int $connect_timeout = 2, int $transmit_timeout = 8, int $max_redirects = 10): array
  {
    try
    {
      $state = [
        'http_user_agent' => 'Mozilla/5.0 (compatible; BNETDocs; +https://bnetdocs.org/)',
        'received_body' => null,
        'received_code' => null,
        'received_content_type' => null,
        'received_json' => null,
        'sent_json' => null,
        'time_end' => null,
        'time_start' => microtime(true),
        'time_total' => null,
        'webhook_url' => $this->webhook_url . (
          $wait ? (strpos($this->webhook_url, '?') === false ? '?' : '&') . 'wait=true' : ''),
      ];

      $curl = curl_init();

      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
      curl_setopt($curl, CURLOPT_TIMEOUT, $transmit_timeout);

      curl_setopt($curl, CURLOPT_AUTOREFERER, true);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl, CURLOPT_MAXREDIRS, $max_redirects);
      curl_setopt($curl, CURLOPT_POSTREDIR, CURL_REDIR_POST_ALL);

      curl_setopt($curl, CURLOPT_URL, $state['webhook_url']);

      $state['sent_json'] = json_encode($this); // uses the return from $this->jsonSerialize()

      if ($state['sent_json'] !== false)
      {
        curl_setopt($curl, CURLOPT_POST, true);
        if (PHP_VERSION >= 5.5 && PHP_VERSION < 7.0)
        {
          // disable processing of @ symbol as a filename in CURLOPT_POSTFIELDS.
          // option introduced in PHP 5.5, deprecated in 7.0.
          curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $state['sent_json']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
          'Content-Type: application/json;charset=utf-8',
          'User-Agent: ' . $state['http_user_agent'],
        ]);
      }

      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

      $state['received_body'] = curl_exec($curl);
      $state['received_code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      $state['received_content_type'] = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

      if (stripos(trim($state['received_content_type']), 'application/json') === 0)
      {
        $state['received_json'] = json_decode(
          $state['received_body'],
          true, // associative array
          512, // max depth (default: 512)
          JSON_OBJECT_AS_ARRAY | JSON_THROW_ON_ERROR // options
        );
      }
    }
    finally
    {
      if ($curl)
      {
        curl_close($curl);
      }

      $state['time_end'] = microtime(true);
      $state['time_total'] = ($state['time_end'] ?? 0) - ($state['time_start'] ?? 0);
      return $state;
    }
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
