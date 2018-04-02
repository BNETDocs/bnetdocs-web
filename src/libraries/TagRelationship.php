<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\Packet;
use \BNETDocs\Libraries\Server;
use \BNETDocs\Libraries\Tag;
use \BNETDocs\Libraries\User;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;

use \InvalidArgumentException;
use \PDO;
use \PDOException;

class TagRelationship {

  const CACHE_TTL = 3600;

  const OBJECT_TYPE_COMMENT = 0;
  const OBJECT_TYPE_DOCUMENT = 1;
  const OBJECT_TYPE_NEWS_POST = 2;
  const OBJECT_TYPE_PACKET = 3;
  const OBJECT_TYPE_SERVER = 4;
  const OBJECT_TYPE_USER = 5;

  private function __construct() {}

  public static function add( $tag_id, $object_id, $object_type ) {

    if ( !isset( Common::$database )) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {

      $stmt = Common::$database->prepare('
        INSERT INTO `tag_relations` ( `object_id`, `object_type`, `tag_id` )
        VALUES ( :object_id, :object_type, :tag_id );
      ');

      $stmt->bindParam( ':object_id', $object_id, PDO::PARAM_INT );
      $stmt->bindParam( ':object_type', $object_type, PDO::PARAM_INT );
      $stmt->bindParam( ':tag_id', $tag_id, PDO::PARAM_INT );

      $stmt->execute();

      $stmt->closeCursor();

      // uncache this object's tags
      $cache_key = 'bnetdocs-tags-' .
        (int) $object_type . '-' . (int) $object_id;
      $cache_val = Common::$cache->delete( $cache_key );

    } catch ( PDOException $e ) {
      throw new QueryException( 'Cannot add tag to object', $e );

    }

  }

  public static function getObjectByType( $object_id, $object_type ) {
    switch ( $object_type ) {
      case self::OBJECT_TYPE_COMMENT: {
        return new Comment( $object_id );
      }
      case self::OBJECT_TYPE_DOCUMENT: {
        return new Document( $object_id );
      }
      case self::OBJECT_TYPE_NEWS_POST: {
        return new NewsPost( $object_id );
      }
      case self::OBJECT_TYPE_PACKET: {
        return new Packet( $object_id );
      }
      case self::OBJECT_TYPE_SERVER: {
        return new Server( $object_id );
      }
      case self::OBJECT_TYPE_USER: {
        return new User( $object_id );
      }
      default: {
        throw new InvalidArgumentException( 'Invalid object type id' );
      }
    }
  }

  public static function getObjectTags( $object_id, $object_type ) {
    $tags = array();

    $cache_key = 'bnetdocs-tags-' .
      (int) $object_type . '-' . (int) $object_id;
    $cache_val = Common::$cache->get( $cache_key );

    if ( $cache_val !== false ) {
      $ids = explode( ',', $cache_val );
      foreach ( $ids as $tag_id ) {
        $tags[] = new Tag( $tag_id );
      }
      return $tags;
    }

    if ( !isset( Common::$database )) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {

      $stmt = Common::$database->prepare('
        SELECT `tag_id` FROM `tag_relations` WHERE
        `object_type` = :object_type AND `object_id` = :object_id;
      ');

      $stmt->bindParam( ':object_type', $object_type, PDO::PARAM_INT );
      $stmt->bindParam( ':object_id', $object_id, PDO::PARAM_INT );

      $ids = array();

      while ( $row = $stmt->fetch( PDO::FETCH_OBJ )) {
        $ids[] = (int) $row['tag_id'];
        $tags[] = new Tag( (int) $row['tag_id'] );
      }

      $stmt->closeCursor();

      Common::$cache->set( $cache_key, implode( ',', $ids ), self::CACHE_TTL );

    } catch ( PDOException $e ) {
      throw new QueryException( 'Cannot get object tags', $e );

    }

    return $tags;
  }

  public static function getObjectType( $alien_object ) {
    if ( $alien_object instanceof Comment )
    {
      return self::OBJECT_TYPE_COMMENT;
    }
    else if ( $alien_object instanceof Document )
    {
      return self::OBJECT_TYPE_DOCUMENT;
    }
    else if ( $alien_object instanceof NewsPost )
    {
      return self::OBJECT_TYPE_NEWS_POST;
    }
    else if ( $alien_object instanceof Packet )
    {
      return self::OBJECT_TYPE_PACKET;
    }
    else if ( $alien_object instanceof Server )
    {
      return self::OBJECT_TYPE_SERVER;
    }
    else if ( $alien_object instanceof User )
    {
      return self::OBJECT_TYPE_USER;
    }
    else
    {
      throw new InvalidArgumentException( 'Cannot detect object type' );
    }
  }

  public static function remove( $tag_id, $object_id, $object_type ) {

    if ( !isset( Common::$database )) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {

      $stmt = Common::$database->prepare('
        DELETE FROM `tag_relations` WHERE
          `tag_id` = :tag_id AND
          `object_type` = :object_type AND
          `object_id` = :object_id
        LIMIT 1;
      ');

      $stmt->bindParam( ':object_id', $object_id, PDO::PARAM_INT );
      $stmt->bindParam( ':object_type', $object_type, PDO::PARAM_INT );
      $stmt->bindParam( ':tag_id', $tag_id, PDO::PARAM_INT );

      $stmt->execute();

      $stmt->closeCursor();

      // uncache this object's tags
      $cache_key = 'bnetdocs-tags-' .
        (int) $object_type . '-' . (int) $object_id;
      $cache_val = Common::$cache->delete( $cache_key );

    } catch ( PDOException $e ) {
      throw new QueryException( 'Cannot remove tag from object', $e );

    }

  }

}
