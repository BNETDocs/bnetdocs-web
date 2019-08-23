<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Activity;
use \BNETDocs\Libraries\ActivityType;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;

class ActivityFeed {

  const DEFAULT_SAMPLE_SIZE = 10;

  protected $activities;

  public function __construct() {
    $this->activities = array();
  }

  public function getActivities(
    $sample_size = self::DEFAULT_SAMPLE_SIZE,
    $target_event_types = null
  ) {

    if ( !isset( Common::$database )) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    if ( $target_event_types === null ) {
      $target_event_types = self::getDefaultTargetEventTypes();
    }

    try {

      if ( $target_event_types === false ) {

        $where_clause = '';

      } else {

        $where_clause = 'WHERE event_type_id IN ('
          . implode( ',', $target_event_types ) . ')'
        ;

      }

      $stmt = Common::$database->prepare('
        SELECT
          event_datetime,
          event_type_id,
          meta_data,
          user_id
        FROM event_log
        ' . $where_clause . '
        ORDER BY id DESC
        LIMIT ' . (int) $sample_size . ';
      ');

      if (!$stmt->execute()) {
        throw new QueryException( 'Cannot refresh activities' );
      }

      $this->activities = array();

      $tz = new DateTimeZone( 'Etc/UTC' );

      while ( $row = $stmt->fetch( PDO::FETCH_OBJ )) {

        $activity = new Activity();

        if ( $row->user_id === null ) {
          $activity->setActor( null );
        } else {
          $activity->setActor( new User( (int) $row->user_id ));
        }

        //$activity->setObject( null ); // TODO: Parse $row->meta_data

        if ( $row->event_datetime === null ) {
          $activity->setTimestamp( null );
        } else {
          $activity->setTimestamp( new DateTime( $row->event_datetime, $tz ));
        }

        $activity->setTypeByEventType( $row->event_type_id );

        $this->activities[] = $activity;

      }

      $stmt->closeCursor();

      return true;

    } catch ( PDOException $e ) {

      throw new QueryException( 'Cannot refresh activities', $e );

    }

    return false;

  }

  protected function getDefaultTargetEventTypes() {
    return array(
      EventTypes::USER_CREATED,
      EventTypes::NEWS_CREATED,
      EventTypes::NEWS_EDITED,
      EventTypes::NEWS_DELETED,
      EventTypes::PACKET_CREATED,
      EventTypes::PACKET_EDITED,
      EventTypes::PACKET_DELETED,
      EventTypes::DOCUMENT_CREATED,
      EventTypes::DOCUMENT_EDITED,
      EventTypes::DOCUMENT_DELETED,
      EventTypes::COMMENT_CREATED_NEWS,
      EventTypes::COMMENT_CREATED_PACKET,
      EventTypes::COMMENT_CREATED_DOCUMENT,
      EventTypes::COMMENT_CREATED_USER,
      EventTypes::COMMENT_EDITED_NEWS,
      EventTypes::COMMENT_EDITED_PACKET,
      EventTypes::COMMENT_EDITED_DOCUMENT,
      EventTypes::COMMENT_EDITED_USER,
      EventTypes::COMMENT_DELETED_NEWS,
      EventTypes::COMMENT_DELETED_PACKET,
      EventTypes::COMMENT_DELETED_DOCUMENT,
      EventTypes::COMMENT_DELETED_USER,
      EventTypes::COMMENT_CREATED_SERVER,
      EventTypes::COMMENT_CREATED_COMMENT,
      EventTypes::COMMENT_EDITED_SERVER,
      EventTypes::COMMENT_EDITED_COMMENT,
      EventTypes::COMMENT_DELETED_SERVER,
      EventTypes::COMMENT_DELETED_COMMENT,
      EventTypes::SERVER_CREATED,
      EventTypes::SERVER_EDITED,
      EventTypes::SERVER_DELETED,
    );
  }

}
