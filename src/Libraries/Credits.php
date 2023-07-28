<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Database;

class Credits
{
  public const DEFAULT_LIMIT = 5;
  public const DEFAULT_ANONYMOUS = 'Anonymous';

  private function __construct()
  {
    throw new \LogicException('This static class cannot be constructed');
  }

  public static function getTotalUsers(): int|false
  {
    $q = Database::instance()->prepare('SELECT COUNT(*) AS `sum` FROM `users`;');
    if (!$q || !$q->execute()) return false;
    $r = $q->fetchObject();
    $q->closeCursor();
    return (int) $r->sum;
  }

  protected static function getTopContributors(string $table, string $anonymous = self::DEFAULT_ANONYMOUS, int $limit = self::DEFAULT_LIMIT): array|false
  {
    $q = Database::instance()->prepare(sprintf('
      SELECT
        `u`.`id` AS `user_id`,
        IFNULL(IFNULL(`u`.`display_name`, `u`.`username`), \'%s\') AS `name`,
        COUNT(`s`.`id`) AS `count`
      FROM `users` AS `u`
      RIGHT JOIN `%s` AS `s` ON `s`.`user_id` = `u`.`id`
      GROUP BY `u`.`id`
      ORDER BY `count` DESC, `s`.`created_datetime` ASC
      LIMIT %d;
    ', $anonymous, $table, $limit));
    if (!$q || !$q->execute()) return false;
    $r = [];
    while ($o = $q->fetchObject()) $r[] = $o;
    $q->closeCursor();
    return $r;
  }

  public static function getTopContributorsByComments(string $anonymous = self::DEFAULT_ANONYMOUS, int $limit = self::DEFAULT_LIMIT): array|false
  {
    return self::getTopContributors('comments', $anonymous, $limit);
  }

  public static function getTopContributorsByDocuments(string $anonymous = self::DEFAULT_ANONYMOUS, int $limit = self::DEFAULT_LIMIT): array|false
  {
    return self::getTopContributors('documents', $anonymous, $limit);
  }

  public static function getTopContributorsByNewsPosts(string $anonymous = self::DEFAULT_ANONYMOUS, int $limit = self::DEFAULT_LIMIT): array|false
  {
    return self::getTopContributors('news_posts', $anonymous, $limit);
  }

  public static function getTopContributorsByPackets(string $anonymous = self::DEFAULT_ANONYMOUS, int $limit = self::DEFAULT_LIMIT): array|false
  {
    return self::getTopContributors('packets', $anonymous, $limit);
  }

  public static function getTopContributorsByServers(string $anonymous = self::DEFAULT_ANONYMOUS, int $limit = self::DEFAULT_LIMIT): array|false
  {
    return self::getTopContributors('servers', $anonymous, $limit);
  }

  public static function getTotalCommentsByUserId(int $user_id): int|false
  {
    return self::getTotalByUserId('comments', $user_id);
  }

  protected static function getTotalByUserId(string $table, int $user_id): int|false
  {
    $q = Database::instance()->prepare(sprintf('SELECT COUNT(*) AS `sum` FROM `%s` WHERE `user_id` = :id;', $table));
    if (!$q || !$q->execute([':id' => $user_id])) return false;
    $o = $q->fetchObject();
    $q->closeCursor();
    return (int) $o->sum;
  }

  public static function getTotalDocumentsByUserId(int $user_id): int|false
  {
    return self::getTotalByUserId('documents', $user_id);
  }

  public static function getTotalNewsPostsByUserId(int $user_id): int|false
  {
    return self::getTotalByUserId('news_posts', $user_id);
  }

  public static function getTotalPacketsByUserId(int $user_id): int|false
  {
    return self::getTotalByUserId('packets', $user_id);
  }

  public static function getTotalServersByUserId(int $user_id): int|false
  {
    return self::getTotalByUserId('servers', $user_id);
  }
}
