#!/usr/bin/env php
<?php /* vim: set colorcolumn=: */
/**
 * @project bnetdocs-web <https://github.com/BNETDocs/bnetdocs-web/>
 *
 * Dec 5 2020 - Generate strings that might resolve, for Blizzard Classic Battle.net servers and other interesting servers.
 */
declare(strict_types=1);

$regions = [ 'account', 'apac', 'api', 'bot', 'cn', 'emea', 'eu', 'eur', 'forever', 'kor', 'kr', 'na', 'par', 'use', 'usw' ];
$envs = [ 'live', 'ptr' ];
$domains = [ 'classic.blizzard.com', 'blizzard.com' ];

$patterns = [
  'connect.{domain}',
  'connect-{env}.{domain}',
  'connect-{region}.{domain}',
  'connect-{env}-{region}-[ab].{domain}',
];

$names = [];

foreach ($patterns as $pattern)
{
  foreach ($domains as $domain)
  {
    foreach ($regions as $region)
    {
      foreach ($envs as $env)
      {
        $name = $pattern;

        $name = str_replace('{domain}', $domain, $name);
        $name = str_replace('{region}', $region, $name);
        $name = str_replace('{env}', $env, $name);

        if (stripos($name, '[ab]') !== false)
        {
          $names[str_ireplace('[ab]', 'a', $name)] = true;
          $names[str_ireplace('[ab]', 'b', $name)] = true;
          $name = str_ireplace('-[ab]', '', $name);
        }

        $names[$name] = true;
      }
    }
  }
}

$names = array_keys($names);
sort($names);

echo json_encode($names, JSON_PRETTY_PRINT) . PHP_EOL;

