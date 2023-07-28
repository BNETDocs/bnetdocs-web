<?php /* vim: set colorcolumn=: */

namespace BNETDocs\Libraries;

use \PDO;

class Database extends PDO
{
  private static ?self $instance = null;

  public function __construct(string $driver = 'mysql')
  {
    $config = \CarlBennett\MVC\Libraries\Common::$config->$driver;
    if (!$config) throw new \LogicException('Database driver config is invalid');

    $character_set = $config->character_set ?? null;
    $database_name = $config->database ?? null;
    $hostname = $config->servers[0]->hostname ?? null;
    $password = $config->password ?? null;
    $port = $config->servers[0]->port ?? null;
    $username = $config->username ?? null;

    $dsn = \sprintf('%s:host=%s;port=%d;dbname=%s',
      $driver, $hostname, $port, $database_name
    );

    parent::__construct($dsn, $username, $password, [
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::MYSQL_ATTR_INIT_COMMAND => \sprintf('SET NAMES \'%s\'', $character_set),
    ]);
  }

  public static function instance(): self
  {
    if (!self::$instance) self::$instance = new self();
    return self::$instance;
  }
}
