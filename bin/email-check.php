#!/usr/bin/env php
<?php /* vim: set colorcolumn=: */
/**
 * @project bnetdocs-web <https://github.com/BNETDocs/bnetdocs-web/>
 *
 * This script will validate an email address against the current
 * configuration and return whether it is allowed or not.
 * Not exhaustive.
 */
$path = __DIR__ . '/../etc/config.phoenix.json';
$json = file_get_contents($path);
$json = json_decode($json);
$email_denylist = $json->email->recipient_denylist_regexp;

if (!isset($argv[1]))
{
  printf("\e[33mUsage: \e[1;33m%s\e[0;0m" . PHP_EOL, $argv[0] . ' <email-address>');
  exit(1);
}
$email = $argv[1];

if (!filter_var($email, FILTER_VALIDATE_EMAIL))
{
  printf("\e[31mInvalid: [\e[1;31m%s\e[0;31m]\e[0m" . PHP_EOL, $email);
  exit(1);
}

foreach ($email_denylist as $_bad_email) {
  printf("\e[33mRegexp: [\e[0m%s\e[33m]\e[0m" . PHP_EOL, $_bad_email);
  if (preg_match($_bad_email, $email)) {
    printf("\e[31mDenied: [\e[1;31m%s\e[0;31m]\e[0m" . PHP_EOL, $email);
    exit(1);
  }
}

printf("\e[32mAllowed: [\e[1;32m%s\e[0;32m]\e[0m" . PHP_EOL, $email);
exit(0);
