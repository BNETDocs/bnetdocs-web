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

$link2->query("TRUNCATE TABLE `packet_used_by`;");

$rows = $link1->query("SELECT `id`, `usedby` FROM `packets` ORDER BY `id` ASC;");
$i = 0; $j = $rows->num_rows;
while ($row = $rows->fetch_object()) {
  $id     = $row->id;
  $usedby = explode(",", str_replace(", ", ",", $row->usedby));
  foreach ($usedby as $product) {
    switch (strtoupper($product)) {
      case "STARCRAFT":           $product = 1398030674; break;
      case "STARCRAFT BROODWAR":  $product = 1397053520; break;
      case "STARCRAFT SHAREWARE": $product = 1397966930; break;
      case "STARCRAFT JAPANESE":  $product = 1246975058; break;
      case "DIABLO I":            $product = 1146246220; break;
      case "DIABLO SHAREWARE":    $product = 1146308690; break;
      case "DIABLO II":           $product = 1144150096; break;
      case "DIABLO II LOD":       $product = 1144144982; break;
      case "WARCRAFT II":         $product = 1462911566; break;
      case "WARCRAFT III":        $product = 1463898675; break;
      case "WARCRAFT III: TFT":   $product = 1462982736; break;
      case "WORLD OF WARCRAFT":   continue;
    }
    $link2->query("INSERT INTO `packet_used_by` (`id`, `bnet_product_id`)"
      . " VALUES (" . $id . "," . $product . ");");
  }
  ++$i;
  $str = sprintf("Processed %d out of %d (%0.0f%%)...", $i, $j, ($i / $j * 100));
  echo "\033[" . strlen($str) . "D" . $str;
}
echo "\n";
$rows->free();

$link1->close();
$link2->close();
