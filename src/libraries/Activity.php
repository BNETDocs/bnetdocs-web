<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\ActivityType;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\User;
use \DateTime;
use \InvalidArgumentException;

class Activity {

  protected $actor;
  protected $object;
  protected $timestamp;
  protected $type;

  public function getActor() {
    return $this->actor;
  }

  public function getObject() {
    return $this->object;
  }

  public function getTimestamp() {
    return $this->timestamp;
  }

  public function getType() {
    return $this->type;
  }

  public function setActor( User $actor ) {
    $this->actor = $actor;
  }

  public function setObject( object &$object ) {
    $this->object = $object;
  }

  public function setTimestamp( DateTime $timestamp ) {
    $this->timestamp = $timestamp;
  }

  public function setType( $type ) {
    $this->type = $type;
  }

  public function setTypeByEventType( $event_type ) {
    if ( !is_numeric( $event_type )) {
      throw new InvalidArgumentException();
    }

    $this->setType( $event_type );
  }

}
