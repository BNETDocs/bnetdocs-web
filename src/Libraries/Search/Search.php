<?php

namespace BNETDocs\Libraries\Search;

use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\News\Post as NewsPost;
use \BNETDocs\Libraries\Packet\Packet;
use \BNETDocs\Libraries\Search\Results;
use \BNETDocs\Libraries\Server\Server;
use \BNETDocs\Libraries\User\User;

class Search
{
    private function __construct() {}

    public static function query(string $user_input): Results|null
    {
        $results = new Results();
        $pdo = \BNETDocs\Libraries\Db\MariaDb::instance();

        $term = trim($user_input);
        if ($term === '') return null;

        $fulltext = preg_replace('/[^\p{L}\p{N}_]+/u', ' ', $term);
        $like = '%' . $term . '%';

        // --- 1. Comments ---
        self::runIdQuery($pdo, $results,
            'SELECT `id` FROM `comments` WHERE
                MATCH(`content`) AGAINST (:fulltext IN BOOLEAN MODE)
                OR `content` LIKE :like
                ORDER BY `created_datetime` DESC, `id` DESC',
            [':fulltext' => $fulltext, ':like' => $like],
            fn($id) => new Comment((int) $id),
            fn($r, $obj) => $r->addComment($obj)
        );

        // --- 2. Documents ---
        self::runIdQuery($pdo, $results,
            'SELECT `id` FROM `documents` WHERE
                MATCH(`title`, `brief`, `content`) AGAINST (:fulltext IN BOOLEAN MODE)
                OR `title` LIKE :like
                OR `brief` LIKE :like
                OR `content` LIKE :like
                ORDER BY IFNULL(`edited_datetime`, `created_datetime`) DESC',
            [':fulltext' => $fulltext, ':like' => $like],
            fn($id) => new Document((int) $id),
            fn($r, $obj) => $r->addDocument($obj)
        );

        // --- 3. News Posts ---
        self::runIdQuery($pdo, $results,
            'SELECT `id` FROM `news_posts` WHERE
                MATCH(`title`, `content`) AGAINST (:fulltext IN BOOLEAN MODE)
                OR `title` LIKE :like
                OR `content` LIKE :like
                ORDER BY IFNULL(`edited_datetime`, `created_datetime`) DESC',
            [':fulltext' => $fulltext, ':like' => $like],
            fn($id) => new NewsPost((int) $id),
            fn($r, $obj) => $r->addNewsPost($obj)
        );

        // --- 4. Packets ---
        self::runIdQuery($pdo, $results,
            'SELECT `id` FROM `packets` WHERE
                MATCH(`packet_name`, `packet_brief`, `packet_format`, `packet_remarks`)
                    AGAINST (:fulltext IN BOOLEAN MODE)
                OR `packet_name` LIKE :like
                OR `packet_brief` LIKE :like
                OR `packet_format` LIKE :like
                OR `packet_remarks` LIKE :like
                ORDER BY `packet_id` ASC, `packet_name` ASC, `packet_direction_id` ASC',
            [':fulltext' => $fulltext, ':like' => $like],
            fn($id) => new Packet((int) $id),
            fn($r, $obj) => $r->addPacket($obj)
        );

        // --- 5. Servers ---
        self::runIdQuery($pdo, $results,
            'SELECT `id` FROM `servers` WHERE
                MATCH(`label`, `address`) AGAINST (:fulltext IN BOOLEAN MODE)
                OR `label` LIKE :like
                OR `address` LIKE :like
                ORDER BY `label` ASC, `address` ASC, `port` ASC',
            [':fulltext' => $fulltext, ':like' => $like],
            fn($id) => new Server((int) $id),
            fn($r, $obj) => $r->addServer($obj)
        );

        // --- 6. Users ---
        self::runIdQuery($pdo, $results,
            'SELECT `id` FROM `users` WHERE
                MATCH(`username`, `display_name`) AGAINST (:fulltext IN BOOLEAN MODE)
                OR `username` LIKE :like
                OR `display_name` LIKE :like
                ORDER BY IFNULL(`display_name`, `username`) ASC',
            [':fulltext' => $fulltext, ':like' => $like],
            fn($id) => new User((int) $id),
            fn($r, $obj) => $r->addUser($obj)
        );

        // --- 6b. User Profiles ---
        self::runIdQuery($pdo, $results,
            'SELECT `user_id` FROM `user_profiles` WHERE
                MATCH(`biography`,
                      `website`,
                      `discord_username`,
                      `github_username`,
                      `reddit_username`,
                      `steam_id`,
                      `facebook_username`,
                      `twitter_username`,
                      `instagram_username`,
                      `skype_username`)
                    AGAINST (:fulltext IN BOOLEAN MODE)
                OR `biography` LIKE :like
                OR `website` LIKE :like
                OR `discord_username` LIKE :like
                OR `github_username` LIKE :like
                OR `reddit_username` LIKE :like
                OR `steam_id` LIKE :like
                OR `facebook_username` LIKE :like
                OR `twitter_username` LIKE :like
                OR `instagram_username` LIKE :like
                OR `skype_username` LIKE :like
                ORDER BY `user_id` ASC',
            [':fulltext' => $fulltext, ':like' => $like],
            fn($id) => new User((int) $id),
            fn($r, $obj) => $r->addUser($obj)
        );

        return $results;
    }

    private static function runIdQuery(
        \PDO $pdo,
        Results $results,
        string $sql,
        array $params,
        callable $buildObject,
        callable $addToResults
    ): void
    {
        try
        {
            $q = $pdo->prepare($sql);
            if (!$q || !$q->execute($params)) return;

            while ($id = $q->fetchColumn())
            {
                $obj = $buildObject((int) $id);
                $addToResults($results, $obj);
            }
        }
        finally
        {
            if ($q) $q->closeCursor();
        }
    }
}
