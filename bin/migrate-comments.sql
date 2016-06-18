START TRANSACTION;
TRUNCATE TABLE `bnetdocs_phoenix`.`comments`;
INSERT INTO `bnetdocs_phoenix`.`comments`
  SELECT
    `id`,
    IF(`pdid` <= 34, 0, 3) AS `parent_type`,
    `pdid` AS `parent_id`,
    `posterid` AS `user_id`,
    `dtstamp` AS `created_datetime`,
    0 AS `edited_count`,
    NULL AS `edited_datetime`,
    `message` AS `content`
  FROM
    `bnetdocs_botdev`.`comments`
  ORDER BY
    `id` ASC;
-- 328 rows affected
COMMIT;
