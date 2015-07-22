<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Common;
use \Memcached;

class Cache {

  const DEFAULT_TTL = 60;

  protected $memcache;

  public function __construct() {
    $this->memcache = new Memcached();
    foreach (Common::$config->memcache as $server) {
      $this->memcache->addServer($server->hostname, $server->port);
    }
  }

  public function delete($key, $wait = 0) {
    return $this->memcache->delete($key, $wait);
  }

  public function get($key) {
    return $this->memcache->get($key);
  }

  public function set($key, $value, $ttl = self::DEFAULT_TTL) {
    if ($ttl < 1) {
      return $this->memcache->set($key, $value, 0);
    } else {
      return $this->memcache->set($key, $value, time() + $ttl);
    }
  }

}
