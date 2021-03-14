#!/usr/bin/env php
<?php /* vim: set colorcolumn=: */
/**
 * @project bnetdocs-web <https://github.com/BNETDocs/bnetdocs-web/>
 *
 * Dec 5 2020 - Resolve DNS entries from JSON input to A/AAAA/CNAME records, intended to be piped from bin/discover-dns.php.
 */
declare(strict_types=1);

$user_input = file_get_contents('php://stdin');
$json = json_decode($user_input);

if (json_last_error() !== 0)
{
  die('json error, check input');
}

$scan = $json;
while ($scan)
{
  $scan = do_scan($scan);
  echo PHP_EOL;
}

function do_scan($names)
{
  $late_scan = [];

  foreach ($names as $name)
  {
    $record_a = dns_get_record($name, DNS_A);
    $record_aaaa = dns_get_record($name, DNS_AAAA);
    $record_cname = dns_get_record($name, DNS_CNAME);
    $records = array_merge($record_a ?? [], $record_aaaa ?? [], $record_cname ?? []);

    if (count($records) == 0)
    {
      printf("%s: %s", $name, PHP_EOL);
    }
    else
    {
      $ips = [];
      foreach ($records as $record)
      {
        if (isset($record['ip']))
        {
          $ips[] = $record['ip'];
        }
        else
        {
          $ips = ['-> ' . $record['target']];
          if (in_array($record['target'], $names))
          {
            $late_scan[] = $record['target']; // add the alias
          }
        }
      }
      sort($ips);
      printf("%s: %s%s", $name, implode(' ', $ips), PHP_EOL);
    }
  }

  return $late_scan;
}
