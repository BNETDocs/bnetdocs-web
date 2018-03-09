<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\PacketDirectionInvalidException;
use \BNETDocs\Libraries\Exceptions\PacketNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\PacketApplicationLayer;
use \BNETDocs\Libraries\PacketTransportLayer;
use \BNETDocs\Libraries\User;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Markdown;

use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;

class Packet {

  const CACHE_TTL = 300;

  const DIRECTION_CLIENT_SERVER = 1;
  const DIRECTION_SERVER_CLIENT = 2;
  const DIRECTION_PEER_TO_PEER  = 3;

  const OPTION_MARKDOWN  = 0x00000001;
  const OPTION_PUBLISHED = 0x00000002;

  protected $created_datetime;
  protected $edited_count;
  protected $edited_datetime;
  protected $id;
  protected $options_bitmask;
  protected $packet_application_layer_id;
  protected $packet_direction_id;
  protected $packet_format;
  protected $packet_id;
  protected $packet_name;
  protected $packet_remarks;
  protected $packet_transport_layer_id;
  protected $user_id;

  public function __construct( $data ) {
    if ( is_numeric( $data )) {

      $this->created_datetime            = null;
      $this->edited_count                = null;
      $this->edited_datetime             = null;
      $this->id                          = (int) $data;
      $this->options_bitmask             = null;
      $this->packet_application_layer_id = null;
      $this->packet_direction_id         = null;
      $this->packet_format               = null;
      $this->packet_id                   = null;
      $this->packet_name                 = null;
      $this->packet_remarks              = null;
      $this->packet_transport_layer_id   = null;
      $this->user_id                     = null;
      $this->refresh();

    } else if ( $data instanceof StdClass ) {

      self::normalize( $data );

      $this->created_datetime            = $data->created_datetime;
      $this->edited_count                = $data->edited_count;
      $this->edited_datetime             = $data->edited_datetime;
      $this->id                          = $data->id;
      $this->options_bitmask             = $data->options_bitmask;
      $this->packet_application_layer_id = $data->packet_application_layer_id;
      $this->packet_direction_id         = $data->packet_direction_id;
      $this->packet_format               = $data->packet_format;
      $this->packet_id                   = $data->packet_id;
      $this->packet_name                 = $data->packet_name;
      $this->packet_remarks              = $data->packet_remarks;
      $this->packet_transport_layer_id   = $data->packet_transport_layer_id;
      $this->user_id                     = $data->user_id;

    } else {

      throw new InvalidArgumentException( 'Cannot use data argument' );

    }
  }

  public static function getAllPackets() {

    $cache_key = 'bnetdocs-packets';
    $cache_val = Common::$cache->get($cache_key);

    if ( $cache_val !== false && !empty( $cache_val )) {
      $ids     = explode(',', $cache_val);
      $objects = [];

      foreach ( $ids as $id ) {
        $objects[] = new self( $id );
      }

      return $objects;
    }

    if ( !isset( Common::$database )) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {

      $stmt = Common::$database->prepare('
        SELECT
          `created_datetime`,
          `edited_count`,
          `edited_datetime`,
          `id`,
          `options_bitmask`,
          `packet_application_layer_id`,
          `packet_direction_id`,
          `packet_format`,
          `packet_id`,
          `packet_name`,
          `packet_remarks`,
          `packet_transport_layer_id`,
          `user_id`
        FROM `packets`
        ORDER BY `id` ASC;
      ');

      if ( !$stmt->execute() ) {
        throw new QueryException( 'Cannot refresh packets' );
      }

      $ids     = [];
      $objects = [];

      while ( $row = $stmt->fetch( PDO::FETCH_OBJ )) {
        $ids[]     = (int) $row->id;
        $objects[] = new self( $row );

        Common::$cache->set(
          'bnetdocs-packet-' . $row->id, serialize( $row ), self::CACHE_TTL
        );
      }

      $stmt->closeCursor();

      Common::$cache->set( $cache_key, implode( ',', $ids ), self::CACHE_TTL );

      return $objects;

    } catch ( PDOException $e ) {

      throw new QueryException( 'Cannot refresh packets', $e );

    }

    return null;
  }

  public static function getAllPacketsBySearch($query) {

    $cache_key = 'bnetdocs-packetsearch-' . hash( 'md5', $query );
    $cache_val = Common::$cache->get( $cache_key );

    if ( $cache_val !== false && !empty( $cache_val )) {
      $ids     = explode( ',', $cache_val );
      $objects = [];

      foreach ( $ids as $id ) {
        $objects[] = new self( $id );
      }

      return $objects;
    }

    if ( !isset( Common::$database )) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {

      $stmt = Common::$database->prepare('
        SELECT
          `created_datetime`,
          `edited_count`,
          `edited_datetime`,
          `id`,
          `options_bitmask`,
          `packet_application_layer_id`,
          `packet_direction_id`,
          `packet_format`,
          `packet_id`,
          `packet_name`,
          `packet_remarks`,
          `packet_transport_layer_id`,
          `user_id`
        FROM `packets`
        WHERE
          MATCH (`packet_remarks`, `packet_format`, `packet_name`)
          AGAINST (:query IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION)
        ;
      ');

      $stmt->bindParam( ':query', $query, PDO::PARAM_STR );

      if ( !$stmt->execute() ) {
        throw new QueryException( 'Cannot search packets' );
      }

      $ids     = [];
      $objects = [];

      while ( $row = $stmt->fetch( PDO::FETCH_OBJ )) {
        $ids[]     = (int) $row->id;
        $objects[] = new self( $row );

        Common::$cache->set(
          'bnetdocs-packet-' . $row->id, serialize( $row ), self::CACHE_TTL
        );
      }

      $stmt->closeCursor();

      Common::$cache->set( $cache_key, implode( ',', $ids ), self::CACHE_TTL );

      return $objects;

    } catch ( PDOException $e ) {

      throw new QueryException( 'Cannot search packets', $e );

    }

    return null;
  }

  public function getCreatedDateTime() {
    if ( is_null( $this->created_datetime )) {
      return $this->created_datetime;
    }

    $tz = new DateTimeZone( 'UTC' );
    $dt = new DateTime( $this->created_datetime );

    $dt->setTimezone( $tz );

    return $dt;
  }

  public function getEditedCount() {
    return $this->edited_count;
  }

  public function getEditedDateTime() {
    if ( is_null( $this->edited_datetime )) {
      return $this->edited_datetime;
    }

    $tz = new DateTimeZone( 'UTC' );
    $dt = new DateTime( $this->edited_datetime );

    $dt->setTimezone( $tz );

    return $dt;
  }

  public function getId() {
    return $this->id;
  }

  public function getOptionsBitmask() {
    return $this->options_bitmask;
  }

  public function getPacketApplicationLayer() {
    return new PacketApplicationLayer( $this->packet_application_layer );
  }

  public function getPacketApplicationLayerId() {
    return $this->packet_application_layer_id;
  }

  public function getPacketDirectionId() {
    return $this->packet_direction_id;
  }

  public function getPacketDirectionLabel() {
    switch ($this->packet_direction_id) {
      case self::DIRECTION_CLIENT_SERVER: return 'Client to Server';
      case self::DIRECTION_SERVER_CLIENT: return 'Server to Client';
      case self::DIRECTION_PEER_TO_PEER:  return 'Peer to Peer';
      default:
        throw new PacketDirectionInvalidException( $this->packet_direction_id );
    }
  }

  public function getPacketDirectionTag() {
    switch ($this->packet_direction_id) {
      case self::DIRECTION_CLIENT_SERVER: return 'C>S';
      case self::DIRECTION_SERVER_CLIENT: return 'S>C';
      case self::DIRECTION_PEER_TO_PEER:  return 'P2P';
      default:
        throw new PacketDirectionInvalidException( $this->packet_direction_id );
    }
  }

  public function getPacketFormat() {
    return $this->packet_format;
  }

  public function getPacketName() {
    return $this->packet_name;
  }

  public function getPacketId( $format = false ) {
    if (!$format) {
      return $this->packet_id;
    }

    return '0x' . strtoupper( substr( '0' . dechex( $this->packet_id ), -2 ));
  }

  public function getPacketRemarks( $prepare ) {
    if ( !$prepare ) {
      return $this->packet_remarks;
    }

    if ( $this->options_bitmask & self::OPTION_MARKDOWN ) {
      $md = new Markdown();
      return $md->text($this->packet_remarks);
    }

    return $this->packet_remarks;
  }

  public function getPacketTransportLayer() {
    return new PacketTransportLayer( $this->packet_transport_layer_id );
  }

  public function getPacketTransportLayerId() {
    return $this->packet_transport_layer_id;
  }

  public static function getPacketsByUserId( $user_id ) {
    if ( !isset( Common::$database )) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {

      $stmt = Common::$database->prepare('
        SELECT
          `created_datetime`,
          `edited_count`,
          `edited_datetime`,
          `id`,
          `options_bitmask`,
          `packet_application_layer_id`,
          `packet_direction_id`,
          `packet_format`,
          `packet_id`,
          `packet_name`,
          `packet_remarks`,
          `packet_transport_layer_id`,
          `user_id`
        FROM `packets`
        WHERE `user_id` = :user_id
        ORDER BY `id` ASC;
      ');

      $stmt->bindParam( ':user_id', $user_id, PDO::PARAM_INT );

      if ( !$stmt->execute() ) {
        throw new QueryException( 'Cannot query packets by user id' );
      }

      $packets = [];

      while ( $row = $stmt->fetch( PDO::FETCH_OBJ )) {
        $packets[] = new self( $row );

        Common::$cache->set(
          'bnetdocs-packet-' . $row->id, serialize( $row ), self::CACHE_TTL
        );
      }

      $stmt->closeCursor();

      return $packets;

    } catch ( PDOException $e ) {

      throw new QueryException( 'Cannot query packets by user id', $e );

    }

    return null;
  }

  public function getPublishedDateTime() {
    if ( !is_null( $this->edited_datetime )) {
      return $this->getEditedDateTime();
    }

    return $this->getCreatedDateTime();
  }

  public function getURI() {
    return Common::relativeUrlToAbsolute(
      '/packet/' . $this->getId() . '/' . Common::sanitizeForUrl(
        $this->getPacketName(), true
      )
    );
  }

  public function getUsedBy() {

    $ckey = 'bnetdocs-packetusedby-' . $this->id;
    $cval = Common::$cache->get($ckey);

    if ( $cval !== false ) {
      return unserialize( $cval );
    }

    if ( !isset( Common::$database )) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {

      $stmt = Common::$database->prepare('
        SELECT
          `bnet_product_id`
        FROM `packet_used_by`
        WHERE `id` = :id
        ORDER BY `id` ASC;
      ');

      $stmt->bindParam( ':id', $this->id, PDO::PARAM_INT );

      if (!$stmt->execute()) {
        throw new QueryException( 'Cannot query packet used by' );
      }

      $values = [];
      while ( $row = $stmt->fetch( PDO::FETCH_OBJ )) {
        $values[] = (int) $row->bnet_product_id;
      }

      $stmt->closeCursor();

      Common::$cache->set( $ckey, serialize( $values ), self::CACHE_TTL );

      return $values;

    } catch ( PDOException $e ) {

      throw new QueryException( 'Cannot query packet used by', $e );

    }

    return null;
  }

  public function getUser() {
    if ( is_null( $this->user_id )) {
      return null;
    }

    return new User( $this->user_id );
  }

  public function getUserId() {
    return $this->user_id;
  }

  protected static function normalize( StdClass &$data ) {

    $data->created_datetime            = (string) $data->created_datetime;
    $data->edited_count                = (int)    $data->edited_count;
    $data->id                          = (int)    $data->id;
    $data->options_bitmask             = (int)    $data->options_bitmask;
    $data->packet_application_layer_id = (int)    $data->packet_application_layer_id;
    $data->packet_direction_id         = (int)    $data->packet_direction_id;
    $data->packet_format               = (string) $data->packet_format;
    $data->packet_id                   = (int)    $data->packet_id;
    $data->packet_name                 = (string) $data->packet_name;
    $data->packet_remarks              = (string) $data->packet_remarks;
    $data->packet_transport_layer_id   = (int)    $data->packet_transport_layer_id;

    if (!is_null($data->edited_datetime))
      $data->edited_datetime = $data->edited_datetime;

    if (!is_null($data->user_id))
      $data->user_id = $data->user_id;

    return true;
  }

  public function refresh() {

    $ckey = 'bnetdocs-packet-' . $this->id;
    $cval = Common::$cache->get( $ckey );

    if ( $cval !== false ) {
      $cval = unserialize( $cval );

      $this->created_datetime            = $cval->created_datetime;
      $this->edited_count                = $cval->edited_count;
      $this->edited_datetime             = $cval->edited_datetime;
      $this->id                          = $cval->id;
      $this->options_bitmask             = $cval->options_bitmask;
      $this->packet_application_layer_id = $cval->packet_application_layer_id;
      $this->packet_direction_id         = $cval->packet_direction_id;
      $this->packet_format               = $cval->packet_format;
      $this->packet_id                   = $cval->packet_id;
      $this->packet_name                 = $cval->packet_name;
      $this->packet_remarks              = $cval->packet_remarks;
      $this->packet_transport_layer_id   = $cval->packet_transport_layer_id;
      $this->user_id                     = $cval->user_id;

      return true;
    }

    if ( !isset( Common::$database )) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {

      $stmt = Common::$database->prepare('
        SELECT
          `created_datetime`,
          `edited_count`,
          `edited_datetime`,
          `id`,
          `options_bitmask`,
          `packet_application_layer_id`,
          `packet_direction_id`,
          `packet_format`,
          `packet_id`,
          `packet_name`,
          `packet_remarks`,
          `packet_transport_layer_id`,
          `user_id`
        FROM `packets`
        WHERE `id` = :id
        LIMIT 1;
      ');

      $stmt->bindParam( ':id', $this->id, PDO::PARAM_INT );

      if ( !$stmt->execute() ) {
        throw new QueryException( 'Cannot refresh packet' );
      } else if ( $stmt->rowCount() == 0 ) {
        throw new PacketNotFoundException( $this->id );
      }

      $row = $stmt->fetch( PDO::FETCH_OBJ );

      $stmt->closeCursor();

      self::normalize( $row );

      $this->created_datetime            = $row->created_datetime;
      $this->edited_count                = $row->edited_count;
      $this->edited_datetime             = $row->edited_datetime;
      $this->id                          = $row->id;
      $this->options_bitmask             = $row->options_bitmask;
      $this->packet_application_layer_id = $row->packet_application_layer_id;
      $this->packet_direction_id         = $row->packet_direction_id;
      $this->packet_format               = $row->packet_format;
      $this->packet_id                   = $row->packet_id;
      $this->packet_name                 = $row->packet_name;
      $this->packet_remarks              = $row->packet_remarks;
      $this->packet_transport_layer_id   = $row->packet_transport_layer_id;
      $this->user_id                     = $row->user_id;

      Common::$cache->set( $ckey, serialize( $row ), self::CACHE_TTL );

      return true;

    } catch ( PDOException $e ) {

      throw new QueryException( 'Cannot refresh packet', $e );

    }

    return false;
  }

  public function setEditedCount( $value ) {
    $this->edited_count = $value;
  }

  public function setEditedDateTime( DateTime $value ) {
    $this->edited_datetime = $value->format( 'Y-m-d H:i:s' );
  }

  public function setMarkdown( $value ) {
    if ( $value ) {
      $this->options_bitmask |= self::OPTION_MARKDOWN;
    } else {
      $this->options_bitmask &= ~self::OPTION_MARKDOWN;
    }
  }

  public function setPacketFormat( $value ) {
    $this->packet_format = $value;
  }

  public function setPacketId( $value ) {
    $this->packet_id = $value;
  }

  public function setPacketName( $value ) {
    $this->packet_name = $value;
  }

  public function setPacketRemarks( $value ) {
    $this->packet_remarks = $value;
  }

  public function setPublished( $value ) {
    if ( $value ) {
      $this->options_bitmask |= self::OPTION_PUBLISHED;
    } else {
      $this->options_bitmask &= ~self::OPTION_PUBLISHED;
    }
  }

  public function update() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {

      $stmt = Common::$database->prepare('
        UPDATE `packets` SET
          `created_datetime` = :dt1,
          `edited_count` = :edit_count,
          `edited_datetime` = :dt2,
          `options_bitmask` = :options,
          `packet_application_layer_id` = :app_layer_id,
          `packet_direction_id` = :direction,
          `packet_format` = :format,
          `packet_name` = :name,
          `packet_remarks` = :remarks,
          `packet_transport_layer_id` = :transport_layer_id,
          `user_id` = :user_id
        WHERE
          `id` = :id
        LIMIT 1;
      ');

      $stmt->bindParam(
        ':app_layer_id', $this->packet_application_layer_id, PDO::PARAM_INT
      );

      $stmt->bindParam(':dt1', $this->created_datetime, PDO::PARAM_STR);
      $stmt->bindParam(':edit_count', $this->edited_count, PDO::PARAM_INT);

      if ( is_null( $this->edited_datetime )) {
        $stmt->bindParam( ':dt2', null, PDO::PARAM_NULL );
      } else {
        $stmt->bindParam( ':dt2', $this->edited_datetime, PDO::PARAM_STR );
      }

      $stmt->bindParam(
        ':direction', $this->packet_direction_id, PDO::PARAM_INT
      );

      $stmt->bindParam( ':format', $this->packet_format, PDO::PARAM_STR );
      $stmt->bindParam( ':id', $this->id, PDO::PARAM_INT );
      $stmt->bindParam( ':name', $this->packet_name, PDO::PARAM_STR );
      $stmt->bindParam( ':options', $this->options_bitmask, PDO::PARAM_INT );
      $stmt->bindParam( ':remarks', $this->packet_remarks, PDO::PARAM_STR );

      $stmt->bindParam(
        ':transport_layer_id', $this->packet_transport_layer_id, PDO::PARAM_INT
      );

      if ( is_null( $this->user_id )) {
        $stmt->bindParam( ':user_id', null, PDO::PARAM_NULL );
      } else {
        $stmt->bindParam( ':user_id', $this->user_id, PDO::PARAM_INT );
      }

      if ( !$stmt->execute() ) {
        throw new QueryException( 'Cannot update packet' );
      }

      $stmt->closeCursor();

      $object                              = new StdClass();
      $object->created_datetime            = $this->created_datetime;
      $object->edited_count                = $this->edited_count;
      $object->edited_datetime             = $this->edited_datetime;
      $object->id                          = $this->id;
      $object->options_bitmask             = $this->options_bitmask;
      $object->packet_application_layer_id = $this->packet_application_layer_id;
      $object->packet_direction_id         = $this->packet_direction_id;
      $object->packet_format               = $this->packet_format;
      $object->packet_id                   = $this->packet_id;
      $object->packet_name                 = $this->packet_name;
      $object->packet_remarks              = $this->packet_remarks;
      $object->packet_transport_layer_id   = $this->packet_transport_layer_id;
      $object->user_id                     = $this->user_id;

      $cache_key = 'bnetdocs-packet-' . $this->id;
      Common::$cache->set( $cache_key, serialize( $object ), self::CACHE_TTL );
      Common::$cache->delete( 'bnetdocs-packets' );

      return true;

    } catch ( PDOException $e ) {

      throw new QueryException( 'Cannot update packet', $e );

    }
    return false;
  }

}
