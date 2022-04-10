<?php

namespace BNETDocs\Libraries\Packet;

use \BNETDocs\Libraries\Packet\Layer;
use \OutOfBoundsException;

class Application extends Layer {

  protected static $table = array(
    1 => array('Battle.net v1 TCP Messages', 'SID'),
    2 => array('Battle.net v1 UDP Messages', 'PKT'),
    3 => array('Diablo II Realm Messages', 'MCP'),
    4 => array('Diablo II In-Game Messages', 'D2GS'),
    5 => array('Warcraft III In-Game Messages', 'W3GS'),
    6 => array('BotNet Messages', 'PACKET'),
    7 => array('BNLS Messages', 'BNLS'),
    8 => array('Starcraft In-Game Messages', 'SCGP'),
    9 => array('Battle.net v2 TCP Messages', 'SID2'),
  );

  protected function assign(int $id) {
    if (!isset(self::$table[$id])) {
      throw new OutOfBoundsException(sprintf(
        'application id: %d not found', $id
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
