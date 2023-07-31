<?php

namespace BNETDocs\Libraries\Discord;

use \BNETDocs\Libraries\Discord\EmbedAuthor;
use \BNETDocs\Libraries\Discord\EmbedField;
use \BNETDocs\Libraries\Discord\EmbedFooter;
use \BNETDocs\Libraries\Discord\EmbedImage;
use \BNETDocs\Libraries\Discord\EmbedProvider;
use \BNETDocs\Libraries\Discord\EmbedThumbnail;
use \BNETDocs\Libraries\Discord\EmbedVideo;
use \DateTimeInterface;
use \LengthException;
use \SplObjectStorage;

// <https://discordapp.com/developers/docs/resources/channel#embed-object>

class Embed implements \JsonSerializable
{
  public const MAX_DESCRIPTION = 2048;
  public const MAX_FIELDS = 25;
  public const MAX_TITLE = 256;

  protected ?EmbedAuthor $author;
  protected int $color;
  protected ?string $description;
  protected ?SplObjectStorage $fields;
  protected ?EmbedFooter $footer;
  protected ?EmbedImage $image;
  protected ?EmbedProvider $provider;
  protected ?EmbedThumbnail $thumbnail;
  protected ?DateTimeInterface $timestamp;
  protected ?string $title;
  protected ?string $type;
  protected ?string $url;
  protected ?EmbedVideo $video;

  public function __construct()
  {
    $this->author = null;
    $this->color = -1;
    $this->description = '';
    $this->fields = new SplObjectStorage();
    $this->footer = null;
    $this->image = null;
    $this->provider = null;
    $this->thumbnail = null;
    $this->timestamp = null;
    $this->title = '';
    $this->type = 'rich';
    $this->url = '';
    $this->video = null;
  }

  public function addField(EmbedField $value): void
  {
    if ($this->fields->count() >= self::MAX_FIELDS)
    {
      throw new \OverflowException(sprintf(
        'Discord forbids adding more than %d fields', self::MAX_FIELDS
      ));
    }

    $this->fields->attach($value);
  }

  public function addFields(array|SplObjectStorage $value): void
  {
    foreach ($value as $key => $value)
    {
      if ($value instanceof EmbedField)
      {
        $this->addField($value);
      }
      else if (\is_string($key) && \is_scalar($value))
      {
        $this->addField(new EmbedField($key, $value, true));
      }
      else
      {
        throw new \UnexpectedValueException('unexpected value in set cannot be sent to Discord');
      }
    }
  }

  public function fieldCount(): int
  {
    return $this->fields->count();
  }

  public function getAuthor(): ?EmbedAuthor
  {
    return $this->author;
  }

  public function getColor(): int
  {
    return $this->color;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function getFooter(): ?EmbedFooter
  {
    return $this->footer;
  }

  public function getImage(): ?EmbedImage
  {
    return $this->image;
  }

  public function getProvider(): ?EmbedProvider
  {
    return $this->provider;
  }

  public function getThumbnail(): ?EmbedThumbnail
  {
    return $this->thumbnail;
  }

  public function getTimestamp(): ?DateTimeInterface
  {
    return $this->timestamp;
  }

  public function getTitle(): ?string
  {
    return $this->title;
  }

  public function getType(): ?string
  {
    return $this->type;
  }

  public function getUrl(): ?string
  {
    return $this->url;
  }

  public function getVideo(): ?EmbedVideo
  {
    return $this->video;
  }

  public function hasField(EmbedField $value): bool
  {
    return $this->fields->contains($value);
  }

  public function jsonSerialize(): mixed
  {
    $r = [
      'author' => $this->author,
      'color' => $this->color,
      'description' => $this->description,
      'fields' => [],
      'footer' => $this->footer,
      'image' => $this->image,
      'provider' => $this->provider,
      'thumbnail' => $this->thumbnail,
      'title' => $this->title,
      'timestamp' => $this->timestamp,
      'type' => $this->type,
      'url' => $this->url,
      'video' => $this->video,
    ];

    if ($r['color'] === -1) unset($r['color']);

    // add each EmbedField
    foreach ($this->fields as $v) $r['fields'][] = $v;

    // remove null or empty-string key-values, format DateTimeInterface objects
    foreach ($r as $k => &$v)
    {
      if (is_null($v) || (is_string($v) && empty($v)))
        unset($r[$k]);
      else if ($v instanceof DateTimeInterface)
        $v = $v->format(DateTimeInterface::ISO8601);
    }

    return $r;
  }

  public function removeAllFields(): void
  {
    $this->fields = new SplObjectStorage();
  }

  public function removeField(EmbedField $value): void
  {
    $this->fields->detach($value);
  }

  public function setAuthor(?EmbedAuthor $value): void
  {
    $this->author = $value;
  }

  public function setColor(?int $color): void
  {
    $this->color = $color;
  }

  public function setDescription(?string $value): void
  {
    if (!empty($value) && strlen($value) > self::MAX_DESCRIPTION)
    {
      throw new LengthException(sprintf(
        'Discord forbids description longer than %d characters', self::MAX_DESCRIPTION
      ));
    }

    $this->description = $value;
  }

  public function setFooter(?EmbedFooter $value): void
  {
    $this->footer = $value;
  }

  public function setImage(?EmbedImage $value): void
  {
    $this->image = $value;
  }

  public function setProvider(?EmbedProvider $value): void
  {
    $this->provider = $value;
  }

  public function setThumbnail(?EmbedThumbnail $value): void
  {
    $this->thumbnail = $value;
  }

  public function setTimestamp(?DateTimeInterface $value): void
  {
    $this->timestamp = $value;
  }

  public function setTitle(?string $value): void
  {
    if (!empty($value) && strlen($value) > self::MAX_TITLE)
    {
      throw new LengthException(sprintf(
        'Discord forbids title longer than %d characters', self::MAX_TITLE
      ));
    }

    $this->title = $value;
  }

  public function setType(?string $value): void
  {
    $this->type = $value;
  }

  public function setUrl(?string $value): void
  {
    $this->url = $value;
  }

  public function setVideo(?EmbedVideo $value): void
  {
    $this->video = $value;
  }
}
