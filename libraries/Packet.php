<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Cache;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\PacketNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Markdown;
use \BNETDocs\Libraries\User;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;

class Packet {

  const OPTION_MARKDOWN  = 0x00000001;
  const OPTION_PUBLISHED = 0x00000002;

  protected $created_datetime;
  protected $edited_count;
  protected $edited_datetime;
  protected $id;
  protected $options_bitmask;
  protected $packet_application_layer_id;
  protected $packet_direction_id;
  protected $packet_format;
  protected $packet_id;
  protected $packet_name;
  protected $packet_remarks;
  protected $packet_transport_layer_id;
  protected $user_id;

  public function __construct($data) {
    if (is_numeric($data)) {
      $this->created_datetime            = null;
      $this->edited_count                = null;
      $this->edited_datetime             = null;
      $this->id                          = (int) $data;
      $this->options_bitmask             = null;
      $this->packet_application_layer_id = null;
      $this->packet_direction_id         = null;
      $this->packet_format               = null;
      $this->packet_id                   = null;
      $this->packet_name                 = null;
      $this->packet_remarks              = null;
      $this->packet_transport_layer_id   = null;
      $this->user_id                     = null;
      $this->refresh();
    } else if ($data instanceof StdClass) {
      self::normalize($data);
      $this->created_datetime            = $data->created_datetime;
      $this->edited_count                = $data->edited_count;
      $this->edited_datetime             = $data->edited_datetime;
      $this->id                          = $data->id;
      $this->options_bitmask             = $data->options_bitmask;
      $this->packet_application_layer_id = $data->packet_application_layer_id;
      $this->packet_direction_id         = $data->packet_direction_id;
      $this->packet_format               = $data->packet_format;
      $this->packet_id                   = $data->packet_id;
      $this->packet_name                 = $data->packet_name;
      $this->packet_remarks              = $data->packet_remarks;
      $this->packet_transport_layer_id   = $data->packet_transport_layer_id;
      $this->user_id                     = $data->user_id;
    } else {
      throw new InvalidArgumentException("Cannot use data argument");
    }
  }
  
  public static function getAllPackets() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `created_datetime`,
          `edited_count`,
          `edited_datetime`,
          `id`,
          `options_bitmask`,
          `packet_application_layer_id`,
          `packet_direction_id`,
          `packet_format`,
          `packet_id`,
          `packet_name`,
          `packet_remarks`,
          `packet_transport_layer_id`,
          `user_id`
        FROM `packets`
        ORDER BY `id` ASC;
      ");
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh packets");
      }
      $packets = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $packets[] = new self($row);
        Common::$cache->set(
          "bnetdocs-packet-" . $row->id, serialize($row), 300
        );
      }
      $stmt->closeCursor();
      return $packets;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh packets", $e);
    }
    return null;
  }

  public function getCreatedDateTime() {
    if (is_null($this->created_datetime)) {
      return $this->created_datetime;
    } else {
      $tz = new DateTimeZone("UTC");
      $dt = new DateTime($this->created_datetime);
      $dt->setTimezone($tz);
      return $dt;
    }
  }

  public function getEditedCount() {
    return $this->edited_count;
  }
  
  public function getEditedDateTime() {
    if (is_null($this->edited_datetime)) {
      return $this->edited_datetime;
    } else {
      $tz = new DateTimeZone("UTC");
      $dt = new DateTime($this->edited_datetime);
      $dt->setTimezone($tz);
      return $dt;
    }
  }

  public function getId() {
    return $this->id;
  }

  public function getOptionsBitmask() {
    return $this->options_bitmask;
  }

  public function getPacketApplicationLayerId() {
    return $this->packet_application_layer_id;
  }

  public function getPacketDirectionId() {
    return $this->packet_direction_id;
  }

  public function getPacketFormat() {
    return $this->packet_format;
  }

  public function getPacketName() {
    return $this->packet_name;
  }

  public function getPacketId() {
    return $this->packet_id;
  }
  
  public function getPacketRemarks($prepare) {
    if (!$prepare) {
      return $this->packet_remarks;
    }
    if ($this->options_bitmask & self::OPTION_MARKDOWN) {
      $md = new Markdown();
      return $md->text($this->packet_remarks);
    } else {
      return $this->packet_remarks;
    }
  }

  public function getPacketTransportLayerId() {
    return $this->packet_transport_layer_id;
  }

  public static function getPacketsByUserId($user_id) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `created_datetime`,
          `edited_count`,
          `edited_datetime`,
          `id`,
          `options_bitmask`,
          `packet_application_layer_id`,
          `packet_direction_id`,
          `packet_format`,
          `packet_id`,
          `packet_name`,
          `packet_remarks`,
          `packet_transport_layer_id`,
          `user_id`
        FROM `packets`
        WHERE `user_id` = :user_id
        ORDER BY `id` ASC;
      ");
      $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot query packets by user id");
      }
      $packets = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $packets[] = new self($row);
        Common::$cache->set(
          "bnetdocs-packet-" . $row->id, serialize($row), 300
        );
      }
      $stmt->closeCursor();
      return $packets;
    } catch (PDOException $e) {
      throw new QueryException("Cannot query packets by user id", $e);
    }
    return null;
  }

  public function getPublishedDateTime() {
    if (!is_null($this->edited_datetime)) {
      return $this->getEditedDateTime();
    } else {
      return $this->getCreatedDateTime();
    }
  }

  public function getUser() {
    if (is_null($this->user_id)) return null;
    return new User($this->user_id);
  }

  public function getUserId() {
    return $this->user_id;
  }

  protected static function normalize(StdClass &$data) {
    $data->created_datetime            = (string) $data->created_datetime;
    $data->edited_count                = (int)    $data->edited_count;
    $data->id                          = (int)    $data->id;
    $data->options_bitmask             = (int)    $data->options_bitmask;
    $data->packet_application_layer_id = (int)    $data->packet_application_layer_id;
    $data->packet_direction_id         = (int)    $data->packet_direction_id;
    $data->packet_format               = (string) $data->packet_format;
    $data->packet_id                   = (int)    $data->packet_id;
    $data->packet_name                 = (string) $data->packet_name;
    $data->packet_remarks              = (string) $data->packet_remarks;
    $data->packet_transport_layer_id   = (int)    $data->packet_transport_layer_id;

    if (!is_null($data->edited_datetime))
      $data->edited_datetime = $data->edited_datetime;

    if (!is_null($data->user_id))  
      $data->user_id = $data->user_id;

    return true;
  }

  public function refresh() {
    $ckey = "bnetdocs-packet-" . $this->id;
    $cval = Common::$cache->get($ckey);
    if ($cval !== false) {
      $cval                              = unserialize($cval);
      $this->created_datetime            = $cval->created_datetime;
      $this->edited_count                = $cval->edited_count;
      $this->edited_datetime             = $cval->edited_datetime;
      $this->id                          = $cval->id;
      $this->options_bitmask             = $cval->options_bitmask;
      $this->packet_application_layer_id = $cval->packet_application_layer_id;
      $this->packet_direction_id         = $cval->packet_direction_id;
      $this->packet_format               = $cval->packet_format;
      $this->packet_id                   = $cval->packet_id;
      $this->packet_name                 = $cval->packet_name;
      $this->packet_remarks              = $cval->packet_remarks;
      $this->packet_transport_layer_id   = $cval->packet_transport_layer_id;
      $this->user_id                     = $cval->user_id;
      return true;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    try {
      $stmt = Common::$database->prepare("
        SELECT
          `created_datetime`,
          `edited_count`,
          `edited_datetime`,
          `id`,
          `options_bitmask`,
          `packet_application_layer_id`,
          `packet_direction_id`,
          `packet_format`,
          `packet_id`,
          `packet_name`,
          `packet_remarks`,
          `packet_transport_layer_id`,
          `user_id`
        FROM `packets`
        WHERE `id` = :id
        LIMIT 1;
      ");
      $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
      if (!$stmt->execute()) {
        throw new QueryException("Cannot refresh packet");
      } else if ($stmt->rowCount() == 0) {
        throw new PacketNotFoundException($this->id);
      }
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $stmt->closeCursor();
      self::normalize($row);
      $this->created_datetime            = $row->created_datetime;
      $this->edited_count                = $row->edited_count;
      $this->edited_datetime             = $row->edited_datetime;
      $this->id                          = $row->id;
      $this->options_bitmask             = $row->options_bitmask;
      $this->packet_application_layer_id = $row->packet_application_layer_id;
      $this->packet_direction_id         = $row->packet_direction_id;
      $this->packet_format               = $row->packet_format;
      $this->packet_id                   = $row->packet_id;
      $this->packet_name                 = $row->packet_name;
      $this->packet_remarks              = $row->packet_remarks;
      $this->packet_transport_layer_id   = $row->packet_transport_layer_id;
      $this->user_id                     = $row->user_id;
      Common::$cache->set($ckey, serialize($row), 300);
      return true;
    } catch (PDOException $e) {
      throw new QueryException("Cannot refresh packet", $e);
    }
    return false;
  }

}
