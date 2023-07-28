<?php

namespace BNETDocs\Libraries\Packet;

class Application extends \BNETDocs\Libraries\Packet\Layer
{
  protected static $table = [
    1 => ['Battle.net v1 TCP Messages', 'SID'],
    2 => ['Battle.net v1 UDP Messages', 'PKT'],
    3 => ['Diablo II Realm Messages', 'MCP'],
    4 => ['Diablo II In-Game Messages', 'D2GS'],
    5 => ['Warcraft III In-Game Messages', 'W3GS'],
    6 => ['BotNet Messages', 'PACKET'],
    7 => ['BNLS Messages', 'BNLS'],
    8 => ['Starcraft In-Game Messages', 'SCGP'],
    9 => ['Battle.net v2 TCP Messages', 'SID2'],
  ];

  protected function assign(int $id): void
  {
    if (!isset(self::$table[$id]))
    {
      throw new \OutOfBoundsException(\sprintf(
        'application id: %d not found', $id
      ));
    }

    $this->id    = $id;
    $this->label = self::$table[$id][0];
    $this->tag   = self::$table[$id][1];
  }

  public static function getAllAsArray(): array
  {
    return self::$table;
  }

  public static function getAllAsObjects(): array
  {
    $r = [];
    $k = \array_keys(self::$table);
    foreach ($k as $id) $r[] = new self($id);
    return $r;
  }
}
