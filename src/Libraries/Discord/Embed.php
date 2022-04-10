<?php

namespace BNETDocs\Libraries\Discord;

use \BNETDocs\Libraries\Discord\EmbedAuthor;
use \BNETDocs\Libraries\Discord\EmbedField;
use \BNETDocs\Libraries\Discord\EmbedFooter;
use \BNETDocs\Libraries\Discord\EmbedImage;
use \BNETDocs\Libraries\Discord\EmbedProvider;
use \BNETDocs\Libraries\Discord\EmbedThumbnail;
use \BNETDocs\Libraries\Discord\EmbedVideo;

use \DateTime;
use \JsonSerializable;
use \LengthException;
use \OverflowException;
use \SplObjectStorage;

// <https://discordapp.com/developers/docs/resources/channel#embed-object>

class Embed implements JsonSerializable {

  const MAX_DESCRIPTION = 2048;
  const MAX_FIELDS      = 25;
  const MAX_TITLE       = 256;

  protected $author;
  protected $color;
  protected $description;
  protected $fields;
  protected $footer;
  protected $image;
  protected $provider;
  protected $thumbnail;
  protected $timestamp;
  protected $title;
  protected $type;
  protected $url;
  protected $video;

  public function __construct() {
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

  public function addField(EmbedField &$field_object) {
    if ($this->fields->count() >= self::MAX_FIELDS) {
      throw new OverflowException(sprintf(
        'Discord forbids adding more than %d fields',
        self::MAX_FIELDS
      ));
    }

    $this->fields->attach($field_object);
  }

  public function fieldCount() {
    return $this->fields->count();
  }

  public function hasField(EmbedField &$field_object) {
    return $this->fields->contains($field_object);
  }

  public function jsonSerialize() {
    // part of JsonSerializable interface
    $r = array();

    if (!empty($this->description)) $r['description'] = $this->description;
    if (!empty($this->title)) $r['title'] = $this->title;
    if (!empty($this->type)) $r['type'] = $this->type;
    if (!empty($this->url)) $r['url'] = $this->url;
    if ($this->author) $r['author'] = $this->author;
    if ($this->color > -1) $r['color'] = $this->color;
    if ($this->fields->count() > 0) $r['fields'] = array();
    if ($this->footer) $r['footer'] = $this->footer;
    if ($this->image) $r['image'] = $this->image;
    if ($this->provider) $r['provider'] = $this->provider;
    if ($this->thumbnail) $r['thumbnail'] = $this->thumbnail;
    if ($this->video) $r['video'] = $this->video;

    if ($this->timestamp) $r['timestamp'] = $this->timestamp->format(
      DateTime::ISO8601
    );

    foreach ($this->fields as $field_object) {
      $r['fields'][] = $field_object;
    }

    return $r;
  }

  public function removeAllFields() {
    $this->fields = new SplObjectStorage();
  }

  public function removeField(EmbedField &$field_object) {
    $this->fields->detach($field_object);
  }

  public function setAuthor(EmbedAuthor &$author_object) {
    $this->author = $author_object;
  }

  public function setColor(int $color) {
    $this->color = $color;
  }

  public function setDescription(string $description) {
    if (strlen($description) > self::MAX_DESCRIPTION) {
      throw new LengthException(sprintf(
        'Discord forbids description longer than %d characters',
        self::MAX_DESCRIPTION
      ));
    }

    $this->title = $title;
  }

  public function setFooter(EmbedFooter &$footer_object) {
    $this->footer = $footer_object;
  }

  public function setImage(EmbedImage &$image_object) {
    $this->image = $image_object;
  }

  public function setProvider(EmbedProvider &$provider_object) {
    $this->provider = $provider_object;
  }

  public function setThumbnail(EmbedThumbnail &$thumbnail_object) {
    $this->thumbnail = $thumbnail_object;
  }

  public function setTimestamp(DateTime $timestamp) {
    $this->timestamp = $timestamp;
  }

  public function setTitle(string $title) {
    if (strlen($title) > self::MAX_TITLE) {
      throw new LengthException(sprintf(
        'Discord forbids title longer than %d characters',
        self::MAX_TITLE
      ));
    }

    $this->title = $title;
  }

  public function setType(string $type) {
    $this->type = $type;
  }

  public function setUrl(string $url) {
    $this->url = $url;
  }

  public function setVideo(EmbedVideo &$video_object) {
    $this->video = $video_object;
  }

}
