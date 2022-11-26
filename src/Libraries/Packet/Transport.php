<?php

namespace BNETDocs\Libraries\Packet;

class Transport extends \BNETDocs\Libraries\Packet\Layer
{
  protected static $table = [
    1 => ['Transmission Control Protocol', 'TCP'],
    2 => ['User Datagram Protocol', 'UDP'],
    3 => ['Internet Control Message Protocol', 'ICMP'],
  ];

  protected function assign(int $id) : void
  {
    if (!isset(self::$table[$id]))
      throw new \OutOfBoundsException(\sprintf(
        'transport id: %d not found', $id
      ));

    $this->id    = $id;
    $this->label = self::$table[$id][0];
    $this->tag   = self::$table[$id][1];
  }

  public static function getAllAsArray() : array
  {
    return self::$table;
  }

  public static function getAllAsObjects() : array
  {
    $r = [];
    $k = array_keys(self::$table);
    foreach ($k as $id) $r[] = new self($id);
    return $r;
  }
}
