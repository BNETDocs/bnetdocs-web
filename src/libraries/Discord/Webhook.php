<?php

namespace BNETDocs\Libraries\Discord;

use \BNETDocs\Libraries\Discord\Embed;

use \CarlBennett\MVC\Libraries\Common;

use \JsonSerializable;
use \LengthException;
use \LogicException;
use \OverflowException;
use \SplObjectStorage;

// <https://discordapp.com/developers/docs/resources/webhook#execute-webhook>

class Webhook implements JsonSerializable {

  const MAX_EMBEDS = 10;

  protected $avatar_url;
  protected $content;
  protected $embeds;
  protected $file;
  protected $tts;
  protected $username;
  protected $wait;
  protected $webhook_url;

  public function __construct($webhook_url) {
    $this->avatar_url  = '';
    $this->content     = '';
    $this->embeds      = new SplObjectStorage();
    $this->file        = '';
    $this->tts         = false;
    $this->username    = '';
    $this->wait        = false;
    $this->webhook_url = $webhook_url;
  }

  public function addEmbed(Embed &$embed_object) {
    if (!empty($this->content) || !empty($this->file)) {
      throw new LogicException(
        'Discord forbids adding embeds with content or file contents'
      );
    }

    if ($this->embeds->count() >= self::MAX_EMBEDS) {
      throw new OverflowException(sprintf(
        'Discord forbids adding more than %d embeds', self::MAX_EMBEDS
      ));
    }

    $this->embeds->attach($embed_object);
  }

  public function embedCount() {
    return $this->embeds->count();
  }

  public function hasEmbed(Embed &$embed_object) {
    return $this->embeds->contains($embed_object);
  }

  public function jsonSerialize() {
    // part of JsonSerializable interface
    $r = array();

    if (!empty($this->avatar_url)) $r['avatar_url'] = $this->avatar_url;
    if (!empty($this->content)) $r['content'] = $this->content;
    if ($this->embeds->count() > 0) $r['embeds'] = array();
    if (!empty($this->file)) $r['file'] = $this->file;
    if (!empty($this->username)) $r['username'] = $this->username;

    foreach ($this->embeds as $embed_object) {
      $r['embeds'][] = $embed_object;
    }

    $r['tts'] = $this->tts;
    $r['wait'] = $this->wait;

    return $r;
  }

  public function removeAllEmbeds() {
    $this->embeds = new SplObjectStorage();
  }

  public function send(int $connect_timeout = 5, int $max_redirects = 10) {
    return Common::curlRequest(
      $this->webhook_url,
      json_encode($this),
      'application/json',
      $connect_timeout,
      $max_redirects
    );
  }

  public function removeEmbed(Embed &$embed_object) {
    $this->embeds->detach($embed_object);
  }

  public function setAvatarUrl(string $avatar_url) {
    $this->avatar_url = $avatar_url;
  }

  public function setContent(string $content) {
    if (!empty($this->file) || $this->embeds->count() > 0) {
      throw new LogicException(
        'Discord forbids adding content with embeds or file contents'
      );
    }

    $this->content = $content;
  }

  public function setFileContents(string $file_content) {
    if (!empty($this->content) || $this->embeds->count() > 0) {
      throw new LogicException(
        'Discord forbids adding file contents with content or embeds'
      );
    }

    $this->file = $file_content;
  }

  public function setTTS(bool $tts) {
    $this->tts = $tts;
  }

  public function setUsername(string $username) {
    $this->username = $username;
  }

  public function setWait(bool $wait) {
    $this->wait = $wait;
  }

  public function setWebhookUrl(string $webhook_url) {
    if (empty($webhook_url)) {
      throw new LengthException('Webhook url must not be empty');
    }

    $this->webhook_url = $webhook_url;
  }

}
