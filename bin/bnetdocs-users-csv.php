#!/usr/bin/env php
<?php /* vim: set expandtab colorcolumn= tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * @project bnetdocs-web <https://github.com/BNETDocs/bnetdocs-web/>
 *
 * Connects to the users table and prints a csv replica to stdout.
 */

function exit_line($line) { fwrite(STDERR, $line . PHP_EOL); exit(1); }

$csv = fopen('php://stdout', 'w');
if (!$csv) { exit_line('Failed to open stdout for writing'); }

$cfg = file_get_contents(__DIR__ . '/../etc/config.phoenix.json');
if (!$cfg) { exit_line('Failed to open and read config'); }

$cfg = json_decode($cfg);
if (!$cfg || json_last_error() !== JSON_ERROR_NONE) { exit_line('Failed to parse json config'); }

$dbsid = 0;
$dbhost = $cfg->mysql->servers[$dbsid]->hostname;
$dbport = $cfg->mysql->servers[$dbsid]->port;

$dbname = $cfg->mysql->database;
$dbuser = $cfg->mysql->username;
$dbpass = $cfg->mysql->password;

$dbchrset = $cfg->mysql->character_set;

$db = new PDO(sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $dbhost, $dbport, $dbname, $dbchrset), $dbuser, $dbpass);
if (!$db) { exit_line('Failed to connect to database'); }

$rs = $db->query('SELECT * FROM `users` ORDER BY `id` ASC;');
if (!$rs) { exit_line('Failed to query database'); }

$cols = array();
for ($i = 0; $i < $rs->columnCount(); $i++)
{
    $cols[] = $rs->getColumnMeta($i)['name'];
}
fputcsv($csv, $cols);

while ($row = $rs->fetch(PDO::FETCH_NUM)) {
    fputcsv($csv, $row);
}

fclose($csv);
exit(0);
