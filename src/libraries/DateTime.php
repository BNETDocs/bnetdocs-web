<?php

namespace BNETDocs\Libraries;

use \DateTime as PHPDateTime;
use \JsonSerializable;

class DateTime extends PHPDateTime implements JsonSerializable {

  const STRING_FORMAT = 'r';

  /**
   * jsonSerialize()
   * Part of the JsonSerializable interface.
   * Changes the representation of this object when using json.
   *
   * @return string The data to be returned to json_encode().
   */
  public function jsonSerialize() {
    return array(
      'iso'  =>       $this->format('r'),
      'unix' => (int) $this->format('U'),
    );
  }

  public function __toString() {
    return $this->format( self::STRING_FORMAT );
  }

}
