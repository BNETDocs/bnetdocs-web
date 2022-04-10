<?php

namespace BNETDocs\Libraries\Packet;

use \BNETDocs\Libraries\Packet\Layer;
use \OutOfBoundsException;

class Transport extends Layer {

  protected static $table = array(
    1 => array('Transmission Control Protocol', 'TCP'),
    2 => array('User Datagram Protocol', 'UDP'),
    3 => array('Internet Control Message Protocol', 'ICMP'),
  );

  protected function assign(int $id) {
    if (!isset(self::$table[$id])) {
      throw new OutOfBoundsException(sprintf(
        'transport id: %d not found', $id
      ));
    }

    $this->id    = $id;
    $this->label = self::$table[$id][0];
    $this->tag   = self::$table[$id][1];
  }

  public static function getAllAsArray() {
    return self::$table;
  }

  public static function getAllAsObjects() {
    $r = array();
    $k = array_keys(self::$table);
    foreach ($k as $id) {
      $r[] = new self($id);
    }
    return $r;
  }

}
