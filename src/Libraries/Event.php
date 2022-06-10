<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\EventType;
use \BNETDocs\Libraries\Exceptions\EventNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;
use \UnexpectedValueException;

class Event {

  protected $event_datetime = null;
  protected $event_type_id  = null;
  protected $id             = null;
  protected $ip_address     = null;
  protected $meta_data      = null;
  protected $user_id        = null;

  public function __construct( $data ) {
    if ( is_numeric( $data ) ) {
      $this->id = (int) $data;
      $this->refresh();
    } else if ( $data instanceof StdClass ) {
      self::normalize( $data, $this );
    } else {
      throw new InvalidArgumentException( 'Cannot use data argument' );
    }
  }

  public static function &getAllEvents(
    $filter_types = null, $order = null, $limit = null, $index = null
  ) {

    if (empty($filter_types)) {
      $where_clause = '';
    } else {
      $where_clause = 'WHERE `event_type_id` IN ('
        . implode( ',', $filter_types ) . ')'
      ;
    }

    if (!(is_numeric($limit) || is_numeric($index))) {
      $limit_clause = '';
    } else if (!is_numeric($index)) {
      $limit_clause = 'LIMIT ' . (int) $limit;
    } else {
      $limit_clause = 'LIMIT ' . (int) $index . ',' . (int) $limit;
    }

    if ( !isset(Common::$database) ) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {

      $stmt = Common::$database->prepare('
        SELECT `event_datetime`,
               `event_type_id`,
               `id`,
               `ip_address`,
               `meta_data`,
               `user_id`
        FROM `event_log`
        ' . $where_clause . '
        ORDER BY
          ' . ($order ? '`' . $order[0] . '` ' . $order[1] . ',' : '') . '
          `id` ' . ($order ? $order[1] : 'ASC') . ' ' . $limit_clause . ';'
      );

      if (!$stmt->execute()) {
        throw new QueryException( 'Cannot refresh all events' );
      }

      $objects = [];
      while ( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
        $objects[] = new self( $row );
      }

      $stmt->closeCursor();
      return $objects;

    } catch ( PDOException $e ) {

      throw new QueryException( 'Cannot refresh all events', $e );

    }

    return null;
  }

  public static function getEventCount($filter_types = null) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {

      if (empty($filter_types)) {
        $where_clause = '';
      } else {
        $where_clause = ' WHERE event_type_id IN ('
          . implode( ',', $filter_types ) . ')'
        ;
      }

      $stmt = Common::$database->prepare(
        'SELECT COUNT(*) FROM `event_log`' . $where_clause . ';'
      );

      if ( !$stmt->execute() ) {
        throw new QueryException( 'Cannot query event count' );
      } else if ( $stmt->rowCount() == 0 ) {
        throw new QueryException( 'Missing result while querying event count' );
      }

      $row = $stmt->fetch( PDO::FETCH_NUM );

      $stmt->closeCursor();

      return (int) $row[0];

    } catch ( PDOException $e ) {

      throw new QueryException( 'Cannot query event count', $e );

    }

    return null;
  }

  public function getEventDateTime() {
    if ( is_null( $this->event_datetime ) ) {
      return $this->event_datetime;
    } else {
      $tz = new DateTimeZone( 'Etc/UTC' );
      $dt = new DateTime( $this->event_datetime );
      $dt->setTimezone( $tz );
      return $dt;
    }
  }

  public function getEventTypeId() {
    return $this->event_type_id;
  }

  public function getEventTypeName() {
    return (string) ( new EventType( $this->event_type_id ) );
  }

  public function getId() {
    return $this->id;
  }

  public function getIPAddress() {
    return $this->ip_address;
  }

  public function getMetadata() {
    return $this->meta_data;
  }

  public function getUser() {
    if ( is_null( $this->user_id ) ) { return null; }
    try {
      return new User( $this->user_id );
    } catch (UnexpectedValueException $e) {
      return null;
    } catch (UserNotFoundException $e) {
      return null;
    }
  }

  public function getUserId() {
    return $this->user_id;
  }

  protected static function normalize(StdClass &$data, Event &$self = null ) {
    $data->event_datetime = (string) $data->event_datetime;
    $data->event_type_id  = (int)    $data->event_type_id;
    $data->id             = (int)    $data->id;

    if ( !is_null( $data->ip_address ) )
      $data->ip_address = (string) $data->ip_address;

    if ( !is_null( $data->meta_data ) )
      $data->meta_data = (string) $data->meta_data;

    if ( !is_null( $data->user_id ) )
      $data->user_id = (int) $data->user_id;

    if ( $self instanceof Event ) {
      $self->event_datetime = $data->event_datetime;
      $self->event_type_id  = $data->event_type_id;
      $self->id             = $data->id;
      $self->ip_address     = $data->ip_address;
      $self->meta_data      = $data->meta_data;
      $self->user_id        = $data->user_id;
    }

    return true;
  }

  public function refresh() {

    if ( !isset( Common::$database ) ) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    try {

      $stmt = Common::$database->prepare('
        SELECT `event_datetime`,
               `event_type_id`,
               `id`,
               `ip_address`,
               `meta_data`,
               `user_id`
        FROM `event_log` WHERE `id` = :id LIMIT 1;
      ');

      $stmt->bindParam( ':id', $this->id, PDO::PARAM_INT );

      if (!$stmt->execute()) {
        throw new QueryException('Cannot refresh event');
      } else if ($stmt->rowCount() == 0) {
        throw new EventNotFoundException( $this->id );
      }

      $row = $stmt->fetch( PDO::FETCH_OBJ );

      $stmt->closeCursor();
      self::normalize( $row, $this );

      return true;

    } catch ( PDOException $e ) {
      throw new QueryException( 'Cannot refresh event', $e );
    }

    return false;
  }

}
