#!/usr/bin/php
<?php

$config1 = json_decode(file_get_contents("../etc/config.redux.json"));
$config2 = json_decode(file_get_contents("../etc/config.phoenix.json"));

$db1_host = $config1->database->hostname;
$db1_user = $config1->database->username;
$db1_pass = $config1->database->password;
$db1_name = $config1->database->name;

$db2_host = $config2->mysql->servers[0]->hostname;
$db2_user = $config2->mysql->username;
$db2_pass = $config2->mysql->password;
$db2_name = $config2->mysql->database;

$link1 = mysqli_connect($db1_host, $db1_user, $db1_pass, $db1_name);
$link2 = mysqli_connect($db2_host, $db2_user, $db2_pass, $db2_name);

const OLD_STATUS_NEW      = 0;
const OLD_STATUS_NORMAL   = 1;
const OLD_STATUS_RESEARCH = 2;
const OLD_STATUS_DEFUNCT  = 3;

const NEW_STATUS_MARKDOWN   = 0x01;
const NEW_STATUS_PUBLISHED  = 0x02;
const NEW_STATUS_DEPRECATED = 0x04;
const NEW_STATUS_RESEARCH   = 0x08;

$rows = $link1->query("SELECT `id`, `status` FROM `packets` ORDER BY `id` ASC;");
$i = 0; $j = $rows->num_rows;
while ($row = $rows->fetch_object()) {

  $id         = $row->id;
  $old_status = $row->status;

  switch ($old_status) {
    case OLD_STATUS_NEW:
    case OLD_STATUS_NORMAL:
      $new_status = NEW_STATUS_PUBLISHED; break;
    case OLD_STATUS_RESEARCH:
      $new_status = NEW_STATUS_RESEARCH; break;
    case OLD_STATUS_DEFUNCT:
      $new_status = NEW_STATUS_DEPRECATED; break;
    default:
      throw new UnexpectedValueException(sprintf('Unexpected packet status code %d on id %d', $old_status, $id));
  }

  $link2->query(sprintf(
    'UPDATE `packets` SET `options_bitmask` = (`options_bitmask` & ~%d) WHERE `id` = %d LIMIT 1;',
    (NEW_STATUS_RESEARCH | NEW_STATUS_DEPRECATED), $id
  ));

  if ($new_status == NEW_STATUS_DEPRECATED || $new_status == NEW_STATUS_RESEARCH) {
    $link2->query(sprintf(
      'UPDATE `packets` SET `options_bitmask` = (`options_bitmask` | %d) WHERE `id` = %d LIMIT 1;',
      $new_status, $id
    ));
  }

  ++$i;
  $str = sprintf("Processed %d out of %d (%0.0f%%)...", $i, $j, ($i / $j * 100));
  echo "\033[" . strlen($str) . "D" . $str;
}
echo "\n";
$rows->free();

$link1->close();
$link2->close();
