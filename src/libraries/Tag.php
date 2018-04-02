<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\TagNotFoundException;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;

use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;

class Tag {

  const CACHE_TTL = 300;

  protected $alias_id;
  protected $description;
  protected $id;
  protected $name;

  public function __construct( $data ) {
    if ( is_numeric( $id )) {
      $this->id = (int) $data;
      $this->refresh();
    } else if ( $data instanceof StdClass ) {
      self::normalize( $data, $this );
    } else {
      throw new InvalidArgumentException( 'Cannot use data argument' );
    }
  }

  public function getAlias() {
    if ( is_null( $this->alias_id )) {
      return null;
    } else {
      return new self( $this->alias_id );
    }
  }

  public function getAliasId() {
    return $this->alias_id;
  }

  public function getDescription() {
    return $this->description;
  }

  public function getId() {
    return $this->id;
  }

  public function getName() {
    return $this->name;
  }

  protected static function normalize( StdClass &$data, Tag &$self = null ) {
    $data->description = (string) $data->description;
    $data->id          = (int)    $data->id;
    $data->name        = (string) $data->name;

    $data->alias_id = (
      is_null( $data->alias_id ) ? null : (int) $data->alias_id
    );

    if ( $self instanceof Tag ) {
      $self->alias_id    = $data->alias_id;
      $self->description = $data->description;
      $self->id          = $data->id;
      $self->name        = $data->name;
    }

    return true;
  }

  public function refresh() {

    $cache_key = 'bnetdocs-tag-' . $this->id;
    $cache_val = Common::$cache->get( $cache_key );

    if ( $cache_val !== false ) {
      $cache_val = unserialize( $cache_val );
      return self::normalize( $cache_val , $this );
    }

    if ( !isset( Common::$database ) ) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {

      $stmt = Common::$database->prepare('
        SELECT `alias_id`, `description`, `id`, `name`
        FROM `tags` WHERE `id` = :id LIMIT 1;
      ');

      $stmt->bindParam( ':id', $this->id, PDO::PARAM_INT );

      if (!$stmt->execute()) {
        throw new QueryException('Cannot refresh tag');
      } else if ($stmt->rowCount() == 0) {
        throw new TagNotFoundException( $this->id );
      }

      $row = $stmt->fetch( PDO::FETCH_OBJ );

      $stmt->closeCursor();
      self::normalize( $row, $this );

      Common::$cache->set( $cache_key, serialize( $row ), self::CACHE_TTL );

      return true;

    } catch ( PDOException $e ) {

      throw new QueryException( 'Cannot refresh event', $e );

    }

    return false;

  }

}
