<?php /* vim: set colorcolumn=: */

namespace BNETDocs\Libraries\Db;

use \BNETDocs\Libraries\Core\Config;
use \PDO;
use \Pdo\Mysql;

class MariaDb extends PDO
{
  private static ?self $instance = null;

  public function __construct(string $driver = 'mysql')
  {
    $character_set = Config::get(\sprintf('%s.character_set', $driver), null, Config::SKIP_DB) ?? null;
    $database_name = Config::get(\sprintf('%s.database', $driver), null, Config::SKIP_DB) ?? null;
    $hostname = Config::get(\sprintf('%s.hostname', $driver), null, Config::SKIP_DB) ?? null;
    $password = Config::get(\sprintf('%s.password', $driver), null, Config::SKIP_DB) ?? null;
    $port = Config::get(\sprintf('%s.port', $driver), null, Config::SKIP_DB) ?? 3306;
    $username = Config::get(\sprintf('%s.username', $driver), null, Config::SKIP_DB) ?? null;

    $dsn = \sprintf('%s:host=%s;port=%d;dbname=%s',
      $driver, $hostname, $port, $database_name
    );

    parent::__construct($dsn, $username, $password, [
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      Mysql::ATTR_INIT_COMMAND => \sprintf('SET NAMES \'%s\'', $character_set),
    ]);
  }

  public static function instance(bool $auto_construct = true): ?self
  {
    if (!self::$instance && $auto_construct) self::$instance = new self();
    return self::$instance;
  }
}
